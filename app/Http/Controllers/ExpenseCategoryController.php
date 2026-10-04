<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')->latest()->paginate(15);
        return view('expense_categories.index', compact('categories'));
    }

    public function create()
    {
        $nextCode = 'EXP-' . str_pad((ExpenseCategory::count() + 1), 2, '0', STR_PAD_LEFT);
        return view('expense_categories.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'code'        => 'required|string|max:30|unique:expense_categories,code',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')->with('success', 'খরচের খাত সফলভাবে তৈরি করা হয়েছে!');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'code'        => 'required|string|max:30|unique:expense_categories,code,' . $expenseCategory->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')->with('success', 'খরচের খাত সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', 'এই খাতের অধীনে খরচের রেকর্ড রয়েছে। ডিলিট করা সম্ভব নয়।');
        }

        $expenseCategory->delete();
        return redirect()->route('expense-categories.index')->with('success', 'খরচের খাত মুছে ফেলা হয়েছে!');
    }
}