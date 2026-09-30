<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->withCount('products')->latest()->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->active()->get();
        return view('categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . rand(100, 999);

        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'ক্যাটাগরি সফলভাবে তৈরি করা হয়েছে!');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->active()->get();
        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'ক্যাটাগরি আপডেট করা হয়েছে!');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'এই ক্যাটাগরিতে পণ্য রয়েছে। ডিলিট করা সম্ভব নয়।');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'ক্যাটাগরি মুছে ফেলা হয়েছে!');
    }
}