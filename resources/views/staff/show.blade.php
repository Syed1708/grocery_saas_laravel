@extends('tyro-dashboard::layouts.admin')

@section('title', 'Staff Profile - ' . $staff->name)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('staff.index') }}">Staff</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $staff->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $staff->name }} ({{ $staff->designation }})</h1>
            <p class="page-description">Department: {{ $staff->department?->name }} • Branch: {{ $staff->branch?->name }} • Phone: {{ $staff->phone }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('advances.create') }}" class="btn btn-primary">Give Advance (অগ্রিম)</a>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Basic Monthly Salary</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">৳{{ number_format($staff->basic_salary, 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Pending Advance Taken (হাওলাত)</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($staff->pendingAdvance(), 2) }}</div>
        </div>
    </div>
</div>

<!-- Payroll History -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Disbursed Salary History (বেতন পরিশোধের বিবরণী)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Payslip No</th>
                    <th>Month & Year</th>
                    <th>Disbursement Date</th>
                    <th style="text-align: right;">Basic</th>
                    <th style="text-align: right;">Advance Deducted</th>
                    <th style="text-align: right;">Net Salary Paid</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $pr)
                <tr>
                    <td><strong style="font-family: monospace;">{{ $pr->payslip_no }}</strong></td>
                    <td><strong style="color: var(--primary);">{{ $pr->month }} {{ $pr->year }}</strong></td>
                    <td>{{ $pr->salary_date->format('M d, Y') }}</td>
                    <td style="text-align: right;">৳{{ number_format($pr->basic_salary, 2) }}</td>
                    <td style="text-align: right; color: var(--danger, #ef4444);">-৳{{ number_format($pr->advance_deduction, 2) }}</td>
                    <td style="text-align: right; font-weight: 800; color: var(--success, #10b981);">৳{{ number_format($pr->net_salary, 2) }}</td>
                    <td style="text-align: right;">
                        <a href="{{ route('payrolls.show', ['payroll' => $pr->id]) }}" class="btn btn-secondary" style="padding: 2px 8px; font-size: 11px;">Payslip</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--muted-foreground); padding: 1.5rem;">No salary history found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection