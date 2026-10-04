<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OtherIncome;
use Illuminate\Http\Request;

class OtherIncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = OtherIncome::with('user');

        if ($request->filled('from_date')) {
            $query->whereDate('income_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('income_date', '<=', $request->to_date);
        }

        $incomes = $query->latest('income_date')->paginate(15)->withQueryString();
        $totalIncomeThisMonth = OtherIncome::whereMonth('income_date', now()->month)
                                           ->whereYear('income_date', now()->year)
                                           ->sum('amount');
        $totalIncomeAll = OtherIncome::sum('amount');

        return view('incomes.index', compact('incomes', 'totalIncomeThisMonth', 'totalIncomeAll'));
    }

    public function create()
    {
        $autoReceipt = 'INC-' . date('ymd') . '-' . rand(100, 999);
        return view('incomes.create', compact('autoReceipt'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'income_source'  => 'required|string|max:191',
            'receipt_no'     => 'required|string|unique:other_incomes,receipt_no',
            'income_date'    => 'required|date',
            'amount'         => 'required|numeric|gt:0',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        $validated['branch_id'] = $activeBranchId;
        $validated['user_id'] = auth()->id();

        OtherIncome::create($validated);

        return redirect()->route('incomes.index')->with('success', 'অন্যান্য আয় সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function edit(OtherIncome $income)
    {
        return view('incomes.edit', compact('income'));
    }

    public function update(Request $request, OtherIncome $income)
    {
        $validated = $request->validate([
            'income_source'  => 'required|string|max:191',
            'receipt_no'     => 'required|string|unique:other_incomes,receipt_no,' . $income->id,
            'income_date'    => 'required|date',
            'amount'         => 'required|numeric|gt:0',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        $income->update($validated);

        return redirect()->route('incomes.index')->with('success', 'আয়ের তথ্য আপডেট করা হয়েছে!');
    }

    public function destroy(OtherIncome $income)
    {
        $income->delete();
        return redirect()->route('incomes.index')->with('success', 'আয়ের রেকর্ড মুছে ফেলা হয়েছে!');
    }
}