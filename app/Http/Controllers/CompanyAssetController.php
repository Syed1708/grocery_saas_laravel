<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CompanyAsset;
use Illuminate\Http\Request;

class CompanyAssetController extends Controller
{
    public function index()
    {
        $assets = CompanyAsset::with('branch')->latest()->paginate(15);
        $totalCost = CompanyAsset::sum('purchase_cost');
        $currentValuation = CompanyAsset::sum('current_value');

        return view('assets.index', compact('assets', 'totalCost', 'currentValuation'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $nextCode = CompanyAsset::generateNextCode(); // 👈 Uses global max ID
        return view('assets.create', compact('branches', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'     => 'nullable|exists:branches,id',
            'name'          => 'required|string|max:191',
            'asset_code'    => 'required|string|max:50|unique:company_assets,asset_code',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'condition'     => 'required|in:good,maintenance,damaged,disposed',
            'notes'         => 'nullable|string',
        ]);

        CompanyAsset::create($validated);

        return redirect()->route('assets.index')->with('success', 'স্থায়ী সম্পদ সফলভাবে এন্ট্রি করা হয়েছে!');
    }

    public function edit(CompanyAsset $asset)
    {
        $branches = Branch::active()->get();
        return view('assets.edit', compact('asset', 'branches'));
    }

    public function update(Request $request, CompanyAsset $asset)
    {
        $validated = $request->validate([
            'branch_id'     => 'nullable|exists:branches,id',
            'name'          => 'required|string|max:191',
            'asset_code'    => 'required|string|max:50|unique:company_assets,asset_code,' . $asset->id,
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'condition'     => 'required|in:good,maintenance,damaged,disposed',
            'notes'         => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'সম্পদের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(CompanyAsset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'স্থায়ী সম্পদ তালিকা থেকে মুছে ফেলা হয়েছে!');
    }
}