<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function create(Supplier $supplier)
    {
        $voucherNo = 'PAY-' . date('ymd') . '-' . rand(100, 999);
        return view('suppliers.payment', compact('supplier', 'voucherNo'));
    }

    public function store(Request $request, Supplier $supplier)
    {
        $request->validate([
            'amount'         => 'required|numeric|gt:0|max:' . $supplier->current_due,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'voucher_no'     => 'required|string|unique:supplier_payments,voucher_no',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        DB::transaction(function () use ($request, $supplier, $activeBranchId) {
            // 1. Record payment
            SupplierPayment::create([
                'branch_id'      => $activeBranchId,
                'supplier_id'    => $supplier->id,
                'payment_date'   => $request->payment_date,
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method,
                'voucher_no'     => $request->voucher_no,
                'reference'      => $request->reference,
                'note'           => $request->note,
            ]);

            // 2. Reduce supplier outstanding due
            $supplier->decrement('current_due', $request->amount);
        });

        return redirect()->route('suppliers.show', ['supplier' => $supplier->id])
            ->with('success', 'মহাজন বাকি সফলভাবে পরিশোধ করা হয়েছে এবং খাতা আপডেট হয়েছে!');
    }
}