<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function create()
    {
        $accounts = Account::active()->get();
        return view('accounts.transfer', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id'   => 'required|exists:accounts,id',
            'amount'          => 'required|numeric|gt:0',
            'transfer_date'   => 'required|date',
            'note'            => 'nullable|string',
        ]);

        $fromAccount = Account::findOrFail($request->from_account_id);
        $toAccount   = Account::findOrFail($request->to_account_id);

        if ($fromAccount->current_balance < $request->amount) {
            return back()->withInput()->with('error', "উৎস অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই! বর্তমান ব্যালেন্স: ৳{$fromAccount->current_balance}");
        }

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $voucherNo = 'TRF-' . date('ymd') . '-' . rand(100, 999);

        DB::transaction(function () use ($request, $fromAccount, $toAccount, $activeBranchId, $voucherNo) {
            // 1. Deduct from source account
            $fromAccount->decrement('current_balance', $request->amount);
            $fromAccount->refresh();

            AccountTransaction::create([
                'branch_id'        => $activeBranchId,
                'account_id'       => $fromAccount->id,
                'type'             => 'transfer_out',
                'amount'           => $request->amount,
                'balance_after'    => $fromAccount->current_balance,
                'transaction_date' => $request->transfer_date,
                'voucher_no'       => $voucherNo,
                'note'             => "হস্তান্তর: {$toAccount->name} তে স্থানান্তর। " . ($request->note ?? ''),
                'user_id'          => auth()->id(),
            ]);

            // 2. Add to destination account
            $toAccount->increment('current_balance', $request->amount);
            $toAccount->refresh();

            AccountTransaction::create([
                'branch_id'        => $activeBranchId,
                'account_id'       => $toAccount->id,
                'type'             => 'transfer_in',
                'amount'           => $request->amount,
                'balance_after'    => $toAccount->current_balance,
                'transaction_date' => $request->transfer_date,
                'voucher_no'       => $voucherNo,
                'note'             => "হস্তান্তর: {$fromAccount->name} থেকে গ্রহণ। " . ($request->note ?? ''),
                'user_id'          => auth()->id(),
            ]);
        });

        return redirect()->route('accounts.index')->with('success', "৳{$request->amount} সফলভাবে {$fromAccount->name} থেকে {$toAccount->name} তে স্থানান্তর করা হয়েছে!");
    }
}