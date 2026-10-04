<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with('account');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->latest('loan_date')->paginate(15)->withQueryString();

        $totalTaken = Loan::where('type', 'taken')->where('status', 'active')->sum('remaining_amount');
        $totalGiven = Loan::where('type', 'given')->where('status', 'active')->sum('remaining_amount');

        return view('loans.index', compact('loans', 'totalTaken', 'totalGiven'));
    }

    public function create()
    {
        $accounts = Account::active()->get();
        return view('loans.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|in:taken,given',
            'person_name' => 'required|string|max:191',
            'phone'       => 'nullable|string|max:20',
            'amount'      => 'required|numeric|gt:0',
            'loan_date'   => 'required|date',
            'due_date'    => 'nullable|date',
            'account_id'  => 'required|exists:accounts,id',
            'notes'       => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $account = Account::findOrFail($request->account_id);

        if ($validated['type'] === 'given' && $account->current_balance < $validated['amount']) {
            return back()->withInput()->with('error', "পর্যাপ্ত ব্যালেন্স নেই! '{$account->name}' অ্যাকাউন্টে ধার দেওয়ার মতো পর্যাপ্ত টাকা নেই।");
        }

        DB::transaction(function () use ($validated, $activeBranchId, $account) {
            $amount = (float) $validated['amount'];
            $validated['branch_id'] = $activeBranchId;
            $validated['total_paid'] = 0.00;
            $validated['remaining_amount'] = $amount;
            $validated['status'] = 'active';

            $loan = Loan::create($validated);

            if ($loan->type === 'taken') {
                // Inflow: Credit the chosen account
                $account->increment('current_balance', $amount);
                $balanceAfter = $account->fresh()->current_balance;

                AccountTransaction::create([
                    'branch_id'        => $activeBranchId,
                    'account_id'       => $account->id,
                    'type'             => 'loan',
                    'amount'           => $amount,
                    'balance_after'    => $balanceAfter,
                    'transaction_date' => $loan->loan_date,
                    'trx_code'         => 'LOAN-RCV-' . strtoupper(Str::random(5)),
                    'note'             => "দোকানের ঋণ গ্রহণ (Loan Taken) from {$loan->person_name}",
                    'user_id'          => auth()->id(),
                ]);
            } else {
                // Outflow: Debit the chosen account
                $account->decrement('current_balance', $amount);
                $balanceAfter = $account->fresh()->current_balance;

                AccountTransaction::create([
                    'branch_id'        => $activeBranchId,
                    'account_id'       => $account->id,
                    'type'             => 'loan',
                    'amount'           => $amount,
                    'balance_after'    => $balanceAfter,
                    'transaction_date' => $loan->loan_date,
                    'trx_code'         => 'LOAN-GIV-' . strtoupper(Str::random(5)),
                    'note'             => "হাওলাত / ধার প্রদান (Loan Given) to {$loan->person_name}",
                    'user_id'          => auth()->id(),
                ]);
            }
        });

        return redirect()->route('loans.index')->with('success', 'ঋণ / হাওলাত রেকর্ড সফলভাবে সংরক্ষণ করা হয়েছে!');
    }

    public function show(Loan $loan)
    {
        $installments = $loan->installments()->with('account')->paginate(10);
        $accounts = Account::active()->get();

        return view('loans.show', compact('loan', 'installments', 'accounts'));
    }

    // ➕ Add Installment / Repayment
    public function addInstallment(Request $request, Loan $loan)
    {
        $request->validate([
            'amount'         => 'required|numeric|gt:0|max:' . $loan->remaining_amount,
            'payment_date'   => 'required|date',
            'account_id'     => 'required|exists:accounts,id',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'note'           => 'nullable|string',
        ]);

        $account = Account::findOrFail($request->account_id);
        $amount = (float) $request->amount;

        // If paying back a loan we took: money leaves our account
        if ($loan->type === 'taken' && $account->current_balance < $amount) {
            return back()->with('error', "পর্যাপ্ত ব্যালেন্স নেই! '{$account->name}' অ্যাকাউন্টে কিস্তি পরিশোধের পর্যাপ্ত ব্যালেন্স নেই।");
        }

        DB::transaction(function () use ($request, $loan, $account, $amount) {
            $voucherNo = 'INST-' . date('ymd') . '-' . rand(100, 999);

            LoanInstallment::create([
                'loan_id'        => $loan->id,
                'account_id'     => $account->id,
                'payment_date'   => $request->payment_date,
                'amount'         => $amount,
                'voucher_no'     => $voucherNo,
                'payment_method' => $request->payment_method,
                'note'           => $request->note,
            ]);

            // Update loan balances
            $newPaid = $loan->total_paid + $amount;
            $newRemaining = max(0, $loan->remaining_amount - $amount);
            $newStatus = ($newRemaining <= 0) ? 'paid' : 'active';

            $loan->update([
                'total_paid'       => $newPaid,
                'remaining_amount' => $newRemaining,
                'status'           => $newStatus,
            ]);

            // Adjust Account Balance & Ledger
            if ($loan->type === 'taken') {
                // Paying back taken loan -> Debit
                $account->decrement('current_balance', $amount);
                $balanceAfter = $account->fresh()->current_balance;

                AccountTransaction::create([
                    'branch_id'        => $loan->branch_id,
                    'account_id'       => $account->id,
                    'type'             => 'loan',
                    'amount'           => $amount,
                    'balance_after'    => $balanceAfter,
                    'transaction_date' => $request->payment_date,
                    'trx_code'         => $voucherNo,
                    'note'             => "ঋণের কিস্তি পরিশোধ (Loan Repaid) to {$loan->person_name}",
                    'user_id'          => auth()->id(),
                ]);
            } else {
                // Collecting repayment on loan given -> Credit
                $account->increment('current_balance', $amount);
                $balanceAfter = $account->fresh()->current_balance;

                AccountTransaction::create([
                    'branch_id'        => $loan->branch_id,
                    'account_id'       => $account->id,
                    'type'             => 'loan',
                    'amount'           => $amount,
                    'balance_after'    => $balanceAfter,
                    'transaction_date' => $request->payment_date,
                    'trx_code'         => $voucherNo,
                    'note'             => "হাওলাত কিস্তি আদায় (Hawlat Collected) from {$loan->person_name}",
                    'user_id'          => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'কিস্তির টাকা সফলভাবে জমা এবং হিসাব সমন্বয় করা হয়েছে!');
    }

    public function destroy(Loan $loan)
    {
        if ($loan->installments()->count() > 0) {
            return back()->with('error', 'এই ঋণের কিস্তির রেকর্ড রয়েছে। মুছে ফেলা সম্ভব নয়।');
        }

        $loan->delete();
        return redirect()->route('loans.index')->with('success', 'রেকর্ড মুছে ফেলা হয়েছে!');
    }
}