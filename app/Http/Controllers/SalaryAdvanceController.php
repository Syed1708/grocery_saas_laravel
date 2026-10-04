<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SalaryAdvance;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        $advances = SalaryAdvance::with('staff.department')->latest('advance_date')->paginate(15);
        $totalPendingAdvance = SalaryAdvance::where('is_deducted', false)->sum('amount');

        return view('advances.index', compact('advances', 'totalPendingAdvance'));
    }

    public function create()
    {
        $staffMembers = StaffProfile::active()->orderBy('name')->get();
        return view('advances.create', compact('staffMembers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id'       => 'required|exists:staff_profiles,id',
            'advance_date'   => 'required|date',
            'amount'         => 'required|numeric|gt:0',
            'payment_method' => 'required|string|in:cash,bkash,nagad,bank',
            'purpose'        => 'nullable|string|max:191',
            'notes'          => 'nullable|string',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $validated['branch_id'] = $activeBranchId;

        SalaryAdvance::create($validated);

        return redirect()->route('advances.index')->with('success', 'অগ্রিম বেতন / হাওলাত সফলভাবে এন্ট্রি করা হয়েছে!');
    }

    public function destroy(SalaryAdvance $advance)
    {
        if ($advance->is_deducted) {
            return back()->with('error', 'এই অগ্রিম টাকা ইতিমধ্যে মাসিক বেতন থেকে কাটা হয়েছে!');
        }

        $advance->delete();
        return redirect()->route('advances.index')->with('success', 'অগ্রিম বেতনের রেকর্ড মুছে ফেলা হয়েছে!');
    }
}