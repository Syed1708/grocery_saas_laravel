<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OwnerTransaction;
use Illuminate\Http\Request;

class OwnerTransactionController extends Controller
{
    public function index()
    {
        $transactions = OwnerTransaction::with('branch')->latest('transaction_date')->paginate(15);
        
        $totalCapital = OwnerTransaction::capital()->sum('amount');
        $totalDrawing = OwnerTransaction::drawing()->sum('amount');
        $netEquity    = $totalCapital - $totalDrawing;

        $branches = Branch::active()->get();

        return view('equity.index', compact('transactions', 'totalCapital', 'totalDrawing', 'netEquity', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:capital,drawing',
            'transaction_date' => 'required|date',
            'amount'           => 'required|numeric|gt:0',
            'payment_method'   => 'required|string|in:cash,bank,bkash,nagad',
            'reference'        => 'nullable|string|max:100',
            'purpose'          => 'nullable|string|max:191',
            'branch_id'        => 'nullable|exists:branches,id',
            'notes'            => 'nullable|string',
        ]);

        OwnerTransaction::create($validated);

        $msg = $validated['type'] === 'capital' 
            ? 'নতুন মূলধন সফলভাবে যুক্ত করা হয়েছে!' 
            : 'ব্যক্তিগত উত্তোলন সফলভাবে এন্ট্রি করা হয়েছে!';

        return back()->with('success', $msg);
    }

    public function destroy(OwnerTransaction $transaction)
    {
        $transaction->delete();
        return back()->with('success', 'লেনদেনটি সফলভাবে মুছে ফেলা হয়েছে!');
    }
}