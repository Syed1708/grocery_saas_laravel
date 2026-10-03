<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function create(Customer $customer)
    {
        $receiptNo = 'REC-' . date('ymd') . '-' . rand(100, 999);
        return view('customers.payment', compact('customer', 'receiptNo'));
    }

    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'amount'         => 'required|numeric|gt:0|max:' . $customer->current_due,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'receipt_no'     => 'required|string|unique:customer_payments,receipt_no',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        DB::transaction(function () use ($request, $customer, $activeBranchId) {
            CustomerPayment::create([
                'branch_id'      => $activeBranchId,
                'customer_id'    => $customer->id,
                'payment_date'   => $request->payment_date,
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method,
                'receipt_no'     => $request->receipt_no,
                'reference'      => $request->reference,
                'note'           => $request->note,
            ]);

            $customer->decrement('current_due', $request->amount);
        });

        return redirect()->route('customers.show', ['customer' => $customer->id])
            ->with('success', 'বাকি টাকা সফলভাবে আদায় ও জমা করা হয়েছে!');
    }
}