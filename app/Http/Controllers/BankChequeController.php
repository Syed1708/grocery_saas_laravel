<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\BankCheque;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankChequeController extends Controller
{

    /**
     * Display a listing of bank cheques with filters & ledger totals.
     */
    public function index(Request $request)
    {
        $query = BankCheque::with(['account', 'user']);

        // 1. Filter by Cheque Type (received / issued)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 2. Filter by Cheque Status (pending / cleared / bounced / cancelled)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Search by Cheque Number, Party Name, or Bank Name
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('cheque_number', 'like', "%{$s}%")
                  ->orWhere('party_name', 'like', "%{$s}%")
                  ->orWhere('bank_name', 'like', "%{$s}%");
            });
        }

        // 4. Optional Date Range Filter
        if ($request->filled('from_date')) {
            $query->whereDate('cheque_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('cheque_date', '<=', $request->to_date);
        }

        // 5. Paginated results preserving active filters
        $cheques = $query->latest('cheque_date')->paginate(15)->withQueryString();

        // 6. Summary Totals (Pending Receivable vs Pending Payable)
        $totalPending  = (float) (BankCheque::where('type', 'received')->where('status', 'pending')->sum('amount') ?? 0);
        $totalCleared   = (float) (BankCheque::where('type', 'issued')->where('status', 'cleared')->sum('amount') ?? 0);

        // 7. Active Accounts for Cheque Clearance dropdown
        $accounts = Account::active()->whereIn('type', ['bank', 'cash'])->get();

        return view('cheques.index', compact(
            'cheques',
            'totalPending',
            'totalCleared',
            'accounts'
        ));
    }
    public function create()
    {
        return view('cheques.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'          => 'required|in:received,issued',
            'party_type'    => 'required|in:customer,supplier,other',
            'party_name'    => 'required|string|max:191',
            'bank_name'     => 'required|string|max:191',
            'cheque_number' => 'required|string|max:100',
            'cheque_date'   => 'required|date',
            'amount'        => 'required|numeric|gt:0',
            'note'          => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $validated['branch_id'] = $activeBranchId;
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        BankCheque::create($validated);

        return redirect()->route('cheques.index')->with('success', 'নতুন চেক সফলভাবে এন্ট্রি করা হয়েছে!');
    }

    public function updateStatus(Request $request, BankCheque $cheque)
    {
        $request->validate([
            'status'        => 'required|in:cleared,bounced,cancelled',
            'account_id'    => 'required_if:status,cleared|nullable|exists:accounts,id',
            'clearing_date' => 'required_if:status,cleared|nullable|date',
        ]);

        if ($cheque->status === 'cleared') {
            return back()->with('error', 'এই চেকটি ইতিপূর্বেই ক্লিয়ার করা হয়েছে!');
        }

        DB::transaction(function () use ($request, $cheque) {
            $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

            if ($request->status === 'cleared') {
                $account = Account::findOrFail($request->account_id);

                if ($cheque->type === 'received') {
                    // Customer cheque cleared -> Add to bank balance
                    $account->increment('current_balance', $cheque->amount);
                    $account->refresh();

                    AccountTransaction::create([
                        'branch_id'        => $activeBranchId,
                        'account_id'       => $account->id,
                        'type'             => 'deposit',
                        'amount'           => $cheque->amount,
                        'balance_after'    => $account->current_balance,
                        'transaction_date' => $request->clearing_date ?? now()->toDateString(),
                        'voucher_no'       => 'CHQ-CLR-' . date('ymd') . '-' . rand(100, 999),
                        'note'             => "চেক ক্লিয়ারিং (প্রাপ্ত চেক #{$cheque->cheque_number}, {$cheque->party_name})",
                        'user_id'          => auth()->id(),
                    ]);
                } else {
                    // Issued cheque cleared -> Deduct from bank balance
                    $account->decrement('current_balance', $cheque->amount);
                    $account->refresh();

                    AccountTransaction::create([
                        'branch_id'        => $activeBranchId,
                        'account_id'       => $account->id,
                        'type'             => 'withdraw',
                        'amount'           => $cheque->amount,
                        'balance_after'    => $account->current_balance,
                        'transaction_date' => $request->clearing_date ?? now()->toDateString(),
                        'voucher_no'       => 'CHQ-DEB-' . date('ymd') . '-' . rand(100, 999),
                        'note'             => "চেক ডেবিট (মহাজন চেক #{$cheque->cheque_number}, {$cheque->party_name})",
                        'user_id'          => auth()->id(),
                    ]);
                }

                $cheque->update([
                    'status'        => 'cleared',
                    'account_id'    => $account->id,
                    'clearing_date' => $request->clearing_date ?? now()->toDateString(),
                ]);
            } else {
                $cheque->update(['status' => $request->status]);
            }
        });

        return redirect()->route('cheques.index')->with('success', 'চেকের স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(BankCheque $cheque)
    {
        if ($cheque->status === 'cleared') {
            return back()->with('error', 'ক্লিয়ার হওয়া চেক মুছে ফেলা সম্ভব নয়।');
        }

        $cheque->delete();
        return redirect()->route('cheques.index')->with('success', 'চেকের রেকর্ড মুছে ফেলা হয়েছে!');
    }
}