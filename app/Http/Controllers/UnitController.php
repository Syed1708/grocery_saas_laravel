<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('baseUnit')->latest()->paginate(15);
        return view('units.index', compact('units'));
    }

    public function create()
    {
        $baseUnits = Unit::whereNull('base_unit_id')->active()->get();
        return view('units.create', compact('baseUnits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'short_code'      => 'required|string|max:20|unique:units,short_code',
            'allow_decimal'   => 'boolean',
            'base_unit_id'    => 'nullable|exists:units,id',
            'conversion_rate' => 'nullable|numeric|gt:0',
            'status'          => 'required|in:active,inactive',
        ]);

        $validated['allow_decimal'] = $request->has('allow_decimal');

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'পরিমাপের একক সফলভাবে সংরক্ষণ করা হয়েছে!');
    }

    public function edit(Unit $unit)
    {
        $baseUnits = Unit::whereNull('base_unit_id')->where('id', '!=', $unit->id)->active()->get();
        return view('units.edit', compact('unit', 'baseUnits'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'short_code'      => 'required|string|max:20|unique:units,short_code,' . $unit->id,
            'allow_decimal'   => 'boolean',
            'base_unit_id'    => 'nullable|exists:units,id',
            'conversion_rate' => 'nullable|numeric|gt:0',
            'status'          => 'required|in:active,inactive',
        ]);

        $validated['allow_decimal'] = $request->has('allow_decimal');

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'এককের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->subUnits()->count() > 0) {
            return back()->with('error', 'এই এককের অধীনে সাব-ইউনিট রয়েছে। প্রথমে সাব-ইউনিটগুলো মুছে ফেলুন।');
        }

        $unit->delete();
        return redirect()->route('units.index')->with('success', 'একক সফলভাবে মুছে ফেলা হয়েছে!');
    }
}