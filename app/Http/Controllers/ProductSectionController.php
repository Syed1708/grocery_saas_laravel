<?php

namespace App\Http\Controllers;

use App\Models\ProductSection;
use Illuminate\Http\Request;

class ProductSectionController extends Controller
{
    public function index()
    {
        $sections = ProductSection::withCount('products')->latest()->paginate(15);
        return view('sections.index', compact('sections'));
    }

    public function create()
    {
        $nextCode = 'SEC-' . str_pad((ProductSection::count() + 1), 2, '0', STR_PAD_LEFT);
        return view('sections.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'code'        => 'required|string|max:20|unique:product_sections,code',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        ProductSection::create($validated);
        return redirect()->route('sections.index')->with('success', 'দোকানের তাক/সেকশন সফলভাবে যোগ করা হয়েছে!');
    }

    public function edit(ProductSection $section)
    {
        return view('sections.edit', compact('section'));
    }

    public function update(Request $request, ProductSection $section)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'code'        => 'required|string|max:20|unique:product_sections,code,' . $section->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $section->update($validated);
        return redirect()->route('sections.index')->with('success', 'সেকশনের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(ProductSection $section)
    {
        if ($section->products()->count() > 0) {
            return back()->with('error', 'এই সেকশনে পণ্য রয়েছে। প্রথমে পণ্যগুলো অন্য সেকশনে স্থানান্তর করুন।');
        }

        $section->delete();
        return redirect()->route('sections.index')->with('success', 'সেকশন মুছে ফেলা হয়েছে!');
    }
}