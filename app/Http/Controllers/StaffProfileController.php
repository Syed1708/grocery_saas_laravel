<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;

class StaffProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffProfile::with(['department', 'branch']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('designation', 'like', "%{$s}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $staff = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::active()->get();
        $totalMonthlyPayroll = StaffProfile::active()->sum('basic_salary');

        return view('staff.index', compact('staff', 'departments', 'totalMonthlyPayroll'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        $branches = Branch::active()->get();
        $users = User::doesntHave('staffProfile')->get();

        return view('staff.create', compact('departments', 'branches', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'phone'           => 'required|string|max:20|unique:staff_profiles,phone',
            'emergency_phone' => 'nullable|string|max:20',
            'nid_number'      => 'nullable|string|max:50',
            'department_id'   => 'required|exists:departments,id',
            'branch_id'       => 'required|exists:branches,id',
            'user_id'         => 'nullable|exists:users,id',
            'designation'     => 'required|string|max:100',
            'joining_date'    => 'required|date',
            'basic_salary'    => 'required|numeric|min:0',
            'daily_allowance' => 'nullable|numeric|min:0',
            'overtime_rate'   => 'nullable|numeric|min:0',
            'address'         => 'nullable|string',
            'status'          => 'required|in:active,inactive,terminated',
        ]);

        StaffProfile::create($validated);

        return redirect()->route('staff.index')->with('success', 'নতুন কর্মচারী সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function edit(StaffProfile $staff)
    {
        $departments = Department::active()->get();
        $branches = Branch::active()->get();

        return view('staff.edit', compact('staff', 'departments', 'branches'));
    }

    public function update(Request $request, StaffProfile $staff)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'phone'           => 'required|string|max:20|unique:staff_profiles,phone,' . $staff->id,
            'emergency_phone' => 'nullable|string|max:20',
            'nid_number'      => 'nullable|string|max:50',
            'department_id'   => 'required|exists:departments,id',
            'branch_id'       => 'required|exists:branches,id',
            'designation'     => 'required|string|max:100',
            'joining_date'    => 'required|date',
            'basic_salary'    => 'required|numeric|min:0',
            'daily_allowance' => 'nullable|numeric|min:0',
            'overtime_rate'   => 'nullable|numeric|min:0',
            'address'         => 'nullable|string',
            'status'          => 'required|in:active,inactive,terminated',
        ]);

        $staff->update($validated);

        return redirect()->route('staff.index')->with('success', 'কর্মচারীর তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function show(StaffProfile $staff)
    {
        $advances = $staff->advances()->latest()->paginate(10);
        $payrolls = $staff->payrolls()->latest()->paginate(10);

        return view('staff.show', compact('staff', 'advances', 'payrolls'));
    }

    public function destroy(StaffProfile $staff)
    {
        if ($staff->payrolls()->count() > 0) {
            return back()->with('error', 'এই কর্মচারীর বেতন হিসেব রয়েছে। প্রোফাইল নিষ্ক্রিয় (Inactive) করুন।');
        }

        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'কর্মচারী মুছে ফেলা হয়েছে!');
    }
}