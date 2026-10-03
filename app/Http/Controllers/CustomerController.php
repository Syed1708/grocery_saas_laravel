<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%");
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $totalDue = Customer::sum('current_due');

        return view('customers.index', compact('customers', 'totalDue'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:191',
            'phone'        => 'required|string|max:20|unique:customers,phone',
            'email'        => 'nullable|email|max:100',
            'address'      => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'opening_due'  => 'nullable|numeric|min:0',
            'status'       => 'required|in:active,inactive',
        ]);

        $validated['credit_limit'] = $validated['credit_limit'] ?? 10000.00;
        $validated['opening_due'] = $validated['opening_due'] ?? 0.00;
        $validated['current_due'] = $validated['opening_due'];

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'নতুন কাস্টমার সফলভাবে যুক্ত হয়েছে!');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:191',
            'phone'        => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email'        => 'nullable|email|max:100',
            'address'      => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'status'       => 'required|in:active,inactive',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'কাস্টমারের তথ্য সফলভাবে আপডেট হয়েছে!');
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()->latest()->paginate(10);
        $payments = $customer->payments()->latest()->paginate(10);

        return view('customers.show', compact('customer', 'sales', 'payments'));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'এই কাস্টমারের বিক্রয় রেকর্ড রয়েছে। ডিলিট করা সম্ভব নয়।');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'কাস্টমার মুছে ফেলা হয়েছে!');
    }
}