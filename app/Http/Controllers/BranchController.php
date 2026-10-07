<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users')->latest()->paginate(10);
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:191',
            'code'    => 'required|string|max:20|unique:branches,code',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'is_main' => 'boolean',
            'status'  => 'required|in:active,inactive',
        ]);

        if (!empty($validated['is_main']) && $validated['is_main']) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'নতুন শাখা সফলভাবে যোগ করা হয়েছে!');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:191',
            'code'    => 'required|string|max:20|unique:branches,code,' . $branch->id,
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'is_main' => 'boolean',
            'status'  => 'required|in:active,inactive',
        ]);

        if (!empty($validated['is_main']) && $validated['is_main']) {
            Branch::where('id', '!=', $branch->id)->update(['is_main' => false]);
        }

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'শাখার তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(Branch $branch)
    {
        $user = auth()->user();

        // 🛡️ Guard: Manager cannot delete unless they have explicit delete privilege or are admin
        if (!$user->isAdmin() && !$user->hasPrivilege('branches.delete')) {
            return back()->with('error', 'আপনার শাখা মুছে ফেলার (Delete) অনুমতি নেই!');
        }
        if ($branch->is_main) {
            return back()->with('error', 'প্রধান শাখা (Main Branch) ডিলিট করা সম্ভব নয়!');
        }

        if ($branch->users()->count() > 0) {
            return back()->with('error', 'এই শাখায় কর্মরত স্টাফ রয়েছে। ডিলিট করার আগে স্টাফদের অন্য শাখায় স্থানান্তর করুন।');
        }

  

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'শাখা সফলভাবে মুছে ফেলা হয়েছে!');
    }

    // Switch Active Working Branch
    public function switch(Branch $branch)
    {
        $user = auth()->user();

        // 🛡️ Security Check: Cashiers CANNOT switch branches!
        if (!$user->isSuperAdmin() && !$user->hasRole('owner')) {
            abort(403, 'ক্যাশিয়ারদের শাখা পরিবর্তন করার অনুমতি নেই।');
        }

        if ($branch->status !== 'active') {
            return back()->with('error', 'এই শাখাটি বর্তমানে নিষ্ক্রিয় রয়েছে।');
        }

        session(['active_branch_id' => $branch->id]);

        return back()->with('success', 'সফলভাবে শাখায় পরিবর্তন করা হয়েছে: ' . $branch->name);
    }
}