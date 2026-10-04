<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::latest()->paginate(15);

        // Calculate summary balances
        $totalBalance = Account::sum('current_balance') ?? 0;
        $cashBalance  = Account::where('type', 'cash')->sum('current_balance') ?? 0;
        $bankBalance  = Account::where('type', 'bank')->sum('current_balance') ?? 0;
        $mfsBalance   = Account::whereIn('type', ['bkash', 'nagad', 'rocket'])->sum('current_balance') ?? 0;

        return view('accounts.index', compact(
            'accounts', 
            'totalBalance', 
            'cashBalance', 
            'bankBalance', 
            'mfsBalance'
        ));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'type'            => 'required|in:cash,bank,bkash,nagad,rocket,other',
            'account_number'  => 'nullable|string|max:100',
            'bank_name'       => 'nullable|string|max:100',
            'branch_name'     => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric|min:0',
            'status'          => 'required|in:active,inactive',
            'notes'           => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $validated['branch_id'] = $activeBranchId;
        $validated['opening_balance'] = (float) ($validated['opening_balance'] ?? 0);
        $validated['current_balance'] = $validated['opening_balance'];

        DB::transaction(function () use ($validated, $activeBranchId) {
            $account = Account::create($validated);

            if ($account->opening_balance > 0) {
                AccountTransaction::create([
                    'branch_id'        => $activeBranchId,
                    'account_id'       => $account->id,
                    'type'             => 'deposit',
                    'amount'           => $account->opening_balance,
                    'balance_after'    => $account->opening_balance,
                    'transaction_date' => now()->toDateString(),
                    'voucher_no'       => 'OPB-' . date('ymd') . '-' . rand(100, 999),
                    'note'             => 'প্রারম্ভিক ব্যালেন্স (Opening Balance)',
                    'user_id'          => auth()->id(),
                ]);
            }
        });

        return redirect()->route('accounts.index')->with('success', 'নতুন ব্যাংক / ক্যাশ অ্যাকাউন্ট সফলভাবে যুক্ত হয়েছে!');
    }

    public function show(Request $request, Account $account)
    {
        $query = $account->transactions()->latest('transaction_date')->latest('id');

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('accounts.show', compact('account', 'transactions'));
    }

    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'type'           => 'required|in:cash,bank,bkash,nagad,rocket,other',
            'account_number' => 'nullable|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
            'branch_name'    => 'nullable|string|max:100',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', 'অ্যাকাউন্টের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(Account $account)
    {
        if ($account->transactions()->count() > 1) {
            return back()->with('error', 'এই অ্যাকাউন্টে লেনদেনের রেকর্ড রয়েছে। এটি মুছে ফেলা সম্ভব নয়।');
        }

        $account->transactions()->delete();
        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'অ্যাকাউন্ট সফলভাবে মুছে ফেলা হয়েছে!');
    }
}