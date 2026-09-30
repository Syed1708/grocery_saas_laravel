<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->latest()->paginate(15);
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'company_name'   => 'nullable|string|max:191',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'status'         => 'required|in:active,inactive',
        ]);

        Brand::create($validated);
        return redirect()->route('brands.index')->with('success', 'কোম্পানি/ব্র্যান্ড সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:191',
            'company_name'   => 'nullable|string|max:191',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'status'         => 'required|in:active,inactive',
        ]);

        $brand->update($validated);
        return redirect()->route('brands.index')->with('success', 'ব্র্যান্ডের তথ্য আপডেট করা হয়েছে!');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', 'এই ব্র্যান্ডের অধীনে পণ্য রয়েছে।');
        }

        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'ব্র্যান্ড মুছে ফেলা হয়েছে!');
    }
}