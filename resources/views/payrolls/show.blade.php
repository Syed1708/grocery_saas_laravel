<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>পে-স্লিপ #{{ $payroll->payslip_no }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Hind Siliguri", sans-serif; background: #fff; color: #000; padding: 20px; font-size: 13px; max-width: 600px; margin: 0 auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .table-slip { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-slip th, .table-slip td { border: 1px solid #cbd5e1; padding: 6px 10px; }
        .table-slip th { background: #f8fafc; text-align: left; font-size: 11px; }
        .no-print { background: #0f172a; color: white; padding: 10px; text-align: center; margin-bottom: 20px; border-radius: 6px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 6px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Print Payslip
        </button>
    </div>

    <!-- Header -->
    <div class="center" style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
        <h2 style="margin: 0; font-size: 18px;">{{ \App\Models\ShopSetting::get('shop_name', 'Madina General Store') }}</h2>
        <div style="font-size: 12px; color: #64748b;">{{ $payroll->branch->name }}</div>
        <h3 style="margin: 6px 0 0; font-size: 14px; text-transform: uppercase;">Salary Payslip (বেতন স্লিপ)</h3>
        <div style="font-size: 11px; font-family: monospace;">#{{ $payroll->payslip_no }} • Month: <strong>{{ $payroll->month }} {{ $payroll->year }}</strong></div>
    </div>

    <!-- Staff Meta -->
    <table style="width: 100%; margin-bottom: 15px; font-size: 12px;">
        <tr>
            <td><strong>Staff Name:</strong> {{ $payroll->staff->name }}</td>
            <td class="right"><strong>Designation:</strong> {{ $payroll->staff->designation }}</td>
        </tr>
        <tr>
            <td><strong>Department:</strong> {{ $payroll->staff->department?->name }}</td>
            <td class="right"><strong>Payment Date:</strong> {{ $payroll->salary_date->format('d M, Y') }}</td>
        </tr>
    </table>

    <!-- Earnings & Deductions Table -->
    <table class="table-slip">
        <thead>
            <tr>
                <th>Earnings (আয়)</th>
                <th class="right">Amount (৳)</th>
                <th>Deductions (কর্তন)</th>
                <th class="right">Amount (৳)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary (মূল বেতন)</td>
                <td class="right">{{ number_format($payroll->basic_salary, 2) }}</td>
                <td>Advance Deducted (অগ্রিম)</td>
                <td class="right" style="color: red;">-{{ number_format($payroll->advance_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Allowance / Bonus (ভাতা)</td>
                <td class="right">{{ number_format($payroll->allowance, 2) }}</td>
                <td>Fine / Absence (জরিমানা)</td>
                <td class="right" style="color: red;">-{{ number_format($payroll->penalty_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime (ওভারটাইম)</td>
                <td class="right">{{ number_format($payroll->overtime_amount, 2) }}</td>
                <td></td>
                <td class="right"></td>
            </tr>
            <tr style="background: #f1f5f9; font-weight: bold;">
                <td>Total Gross Earnings</td>
                <td class="right">৳{{ number_format($payroll->basic_salary + $payroll->allowance + $payroll->overtime_amount, 2) }}</td>
                <td>Total Deductions</td>
                <td class="right" style="color: red;">-৳{{ number_format($payroll->advance_deduction + $payroll->penalty_deduction, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; margin-top: 15px; font-size: 15px; font-weight: bold; background: #e2e8f0; padding: 10px; border-radius: 4px;">
        <span>Net Salary Paid (নিট প্রদেয় বেতন):</span>
        <span>৳{{ number_format($payroll->net_salary, 2) }}</span>
    </div>

    <!-- Signatures -->
    <div style="display: flex; justify-content: space-between; margin-top: 60px; font-size: 11px;">
        <div style="border-top: 1px solid #000; padding-top: 4px; width: 140px; text-align: center;">
            Staff Signature
        </div>
        <div style="border-top: 1px solid #000; padding-top: 4px; width: 140px; text-align: center;">
            Authorized By
        </div>
    </div>
</body>
</html>