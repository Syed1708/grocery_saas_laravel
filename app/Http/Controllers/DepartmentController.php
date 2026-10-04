<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('staff')->latest()->paginate(15);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        $nextCode = 'DEPT-' . str_pad((Department::count() + 1), 2, '0', STR_PAD_LEFT);
        return view('departments.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'code'        => 'required|string|max:30|unique:departments,code',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        Department::create($validated);
        return redirect()->route('departments.index')->with('success', 'বিভাগ সফলভাবে যোগ করা হয়েছে!');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'code'        => 'required|string|max:30|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $department->update($validated);
        return redirect()->route('departments.index')->with('success', 'বিভাগের তথ্য আপডেট করা হয়েছে!');
    }

    public function destroy(Department $department)
    {
        if ($department->staff()->count() > 0) {
            return back()->with('error', 'এই বিভাগের অধীনে স্টাফ রয়েছে। ডিলিট করা সম্ভব নয়।');
        }

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'বিভাগ মুছে ফেলা হয়েছে!');
    }
}