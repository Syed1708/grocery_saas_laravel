<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user', 'branch']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();
        
        $totalThisMonth = Expense::whereMonth('expense_date', now()->month)
                                 ->whereYear('expense_date', now()->year)
                                 ->sum('amount');
        $totalOverall = Expense::sum('amount');
        $categories = ExpenseCategory::active()->get();

        return view('expenses.index', compact('expenses', 'totalThisMonth', 'totalOverall', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->get();
        $autoVoucher = 'EXP-' . date('ymd') . '-' . rand(100, 999);
        return view('expenses.create', compact('categories', 'autoVoucher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:expense_categories,id',
            'voucher_no'     => 'required|string|unique:expenses,voucher_no',
            'expense_date'   => 'required|date',
            'amount'         => 'required|numeric|gt:0',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        $validated['branch_id'] = $activeBranchId;
        $validated['user_id'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'দৈনিক খরচ সফলভাবে এন্ট্রি করা হয়েছে!');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:expense_categories,id',
            'voucher_no'     => 'required|string|unique:expenses,voucher_no,' . $expense->id,
            'expense_date'   => 'required|date',
            'amount'         => 'required|numeric|gt:0',
            'payment_method' => 'required|string|in:cash,bank,bkash,nagad',
            'reference'      => 'nullable|string|max:100',
            'note'           => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'খরচের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'খরচের রেকর্ড মুছে ফেলা হয়েছে!');
    }
}