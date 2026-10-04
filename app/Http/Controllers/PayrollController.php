<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Payroll;
use App\Models\SalaryAdvance;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with('staff.department');

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $payrolls = $query->latest('salary_date')->paginate(15)->withQueryString();
        $totalPaidThisMonth = Payroll::whereMonth('salary_date', now()->month)->sum('net_salary');

        return view('payrolls.index', compact('payrolls', 'totalPaidThisMonth'));
    }

    public function create()
    {
        $staffMembers = StaffProfile::with('department')->active()->get();
        $currentMonth = date('F'); // e.g. October
        $currentYear = (int) date('Y');

        return view('payrolls.create', compact('staffMembers', 'currentMonth', 'currentYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id'          => 'required|exists:staff_profiles,id',
            'month'             => 'required|string',
            'year'              => 'required|integer',
            'salary_date'       => 'required|date',
            'basic_salary'      => 'required|numeric|min:0',
            'allowance'         => 'nullable|numeric|min:0',
            'overtime_amount'   => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'penalty_deduction' => 'nullable|numeric|min:0',
            'payment_method'    => 'required|string|in:cash,bank,bkash',
        ]);

        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;

        // Check if salary already generated for this month
        $exists = Payroll::where('staff_id', $request->staff_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()->with('error', "এই কর্মচারীর {$request->month} {$request->year} মাসের বেতন ইতিমধ্যে দেওয়া হয়েছে!");
        }

        $basic = (float) $request->basic_salary;
        $allowance = (float) $request->input('allowance', 0);
        $overtime = (float) $request->input('overtime_amount', 0);
        $advanceDeduct = (float) $request->input('advance_deduction', 0);
        $penalty = (float) $request->input('penalty_deduction', 0);

        $netSalary = max(0, ($basic + $allowance + $overtime) - ($advanceDeduct + $penalty));
        $payslipNo = 'PAY-' . substr($request->year, 2) . strtoupper(substr($request->month, 0, 3)) . '-' . rand(100, 999);

        DB::transaction(function () use ($request, $activeBranchId, $basic, $allowance, $overtime, $advanceDeduct, $penalty, $netSalary, $payslipNo) {
            // 1. Create Payroll
            $payroll = Payroll::create([
                'branch_id'         => $activeBranchId,
                'staff_id'          => $request->staff_id,
                'month'             => $request->month,
                'year'              => $request->year,
                'salary_date'       => $request->salary_date,
                'basic_salary'      => $basic,
                'allowance'         => $allowance,
                'overtime_amount'   => $overtime,
                'advance_deduction' => $advanceDeduct,
                'penalty_deduction' => $penalty,
                'net_salary'        => $netSalary,
                'payment_method'    => $request->payment_method,
                'payment_status'    => 'paid',
                'payslip_no'        => $payslipNo,
                'notes'             => $request->notes,
            ]);

            // 2. Mark pending advances as deducted
            if ($advanceDeduct > 0) {
                SalaryAdvance::where('staff_id', $request->staff_id)
                    ->where('is_deducted', false)
                    ->update(['is_deducted' => true]);
            }
        });

        return redirect()->route('payrolls.index')->with('success', 'মাসিক বেতন সফলভাবে প্রদান ও পে-স্লিপ জেনারেট হয়েছে!');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['staff.department', 'branch']);
        return view('payrolls.show', compact('payroll'));
    }
}