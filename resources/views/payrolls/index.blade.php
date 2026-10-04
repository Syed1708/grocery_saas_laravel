@extends('tyro-dashboard::layouts.admin')

@section('title', 'Monthly Payroll')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Payroll</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Monthly Payroll & Salary Slips (মাসিক বেতন বিতরণ)</h1>
            <p class="page-description">Generate monthly salary disbursements with automatic advance deductions.</p>
        </div>
        <a href="{{ route('payrolls.create') }}" class="btn btn-primary">
            + Disburse Monthly Salary
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Salaries Disbursed This Month</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success, #10b981); margin-top: 0.25rem;">৳{{ number_format($totalPaidThisMonth, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--success, #10b981); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">💵</div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--success, #10b981); background: rgba(16, 185, 129, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--success, #10b981); font-weight: 500; font-size: 0.875rem;">
        {{ session('success') }}
    </div>
</div>
@endif

<div class="card">
    @if($payrolls->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Payslip No</th>
                    <th>Staff Name</th>
                    <th>Designation</th>
                    <th>Salary Month</th>
                    <th style="text-align: right;">Basic Salary</th>
                    <th style="text-align: right;">Advance Deducted</th>
                    <th style="text-align: right;">Net Salary Paid</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrolls as $p)
                <tr>
                    <td><strong style="font-family: monospace;">{{ $p->payslip_no }}</strong></td>
                    <td><strong>{{ $p->staff?->name }}</strong></td>
                    <td>{{ $p->staff?->designation }}</td>
                    <td><span class="badge badge-primary">{{ $p->month }} {{ $p->year }}</span></td>
                    <td style="text-align: right;">৳{{ number_format($p->basic_salary, 2) }}</td>
                    <td style="text-align: right; color: var(--danger, #ef4444);">-৳{{ number_format($p->advance_deduction, 2) }}</td>
                    <td style="text-align: right; font-weight: 800; color: var(--success, #10b981);">৳{{ number_format($p->net_salary, 2) }}</td>
                    <td style="text-align: right;">
                        <a href="{{ route('payrolls.show', ['payroll' => $p->id]) }}" target="_blank" class="btn btn-secondary" style="padding: 2px 8px; font-size: 11px;">
                            🖨️ Payslip
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages()) <div class="pagination">{{ $payrolls->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No salary payments generated</h3>
        <p class="empty-state-description">Generate monthly salary payslips for active branch employees.</p>
        <a href="{{ route('payrolls.create') }}" class="btn btn-primary">Disburse Salary</a>
    </div>
    @endif
</div>
@endsection