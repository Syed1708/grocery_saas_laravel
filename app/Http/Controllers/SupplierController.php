<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%");
            });
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();
        $totalDue = Supplier::sum('current_due');

        return view('suppliers.index', compact('suppliers', 'totalDue'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'company_name'   => 'nullable|string|max:191',
            'phone'          => 'required|string|max:20|unique:suppliers,phone',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:100',
            'opening_due'    => 'nullable|numeric|min:0',
            'status'         => 'required|in:active,inactive',
        ]);

        $validated['opening_due'] = $validated['opening_due'] ?? 0.00;
        $validated['current_due'] = $validated['opening_due'];

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'মহাজন / ডিলার সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'company_name'   => 'nullable|string|max:191',
            'phone'          => 'required|string|max:20|unique:suppliers,phone,' . $supplier->id,
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:100',
            'status'         => 'required|in:active,inactive',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'মহাজনের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->latest()->paginate(10);
        $payments = $supplier->payments()->latest()->paginate(10);

        return view('suppliers.show', compact('supplier', 'purchases', 'payments'));
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->count() > 0) {
            return back()->with('error', 'এই মহাজনের অধীনে ক্রয়কৃত চালান রয়েছে। ডিলিট করা সম্ভব নয়।');
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'মহাজন মুছে ফেলা হয়েছে!');
    }
}