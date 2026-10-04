@extends('tyro-dashboard::layouts.admin')

@section('title', 'Disburse Salary')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('payrolls.index') }}">Payroll</a>
<span class="breadcrumb-separator">/</span>
<span>Disburse</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Disburse Monthly Salary (মাসিক বেতন প্রদান)</h1>
            <p class="page-description">Auto-calculates overtime, bonuses, and deducts pending cash advances.</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('payrolls.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="staff_id">Staff Member <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="staff_id" name="staff_id" class="form-select" required onchange="onPayrollStaffSelect(this)">
                        <option value="" data-basic="0" data-advance="0">-- Select Staff --</option>
                        @foreach($staffMembers as $st)
                            <option value="{{ $st->id }}" data-basic="{{ $st->basic_salary }}" data-advance="{{ $st->pendingAdvance() }}">
                                {{ $st->name }} ({{ $st->designation }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="month">Month <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="month" name="month" class="form-select" required>
                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                            <option value="{{ $m }}" {{ $currentMonth === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="year">Year <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" id="year" name="year" class="form-input" value="{{ $currentYear }}" required>
                </div>
            </div>

            <!-- Salary Calculation Card -->
            <div class="card" style="margin-bottom: 1.5rem; background: var(--muted, rgba(148, 163, 184, 0.08));">
                <div class="card-body" style="padding: 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 0.75rem;">
                        Salary Breakdown & Deductions
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-size: 11px;">Basic Salary (৳)</label>
                            <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-input" value="0.00" required oninput="calcNetSalary()">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 11px;">Allowance / Bonus (+)</label>
                            <input type="number" step="0.01" id="allowance" name="allowance" class="form-input" value="0.00" oninput="calcNetSalary()">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 11px;">Overtime (+)</label>
                            <input type="number" step="0.01" id="overtime_amount" name="overtime_amount" class="form-input" value="0.00" oninput="calcNetSalary()">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-size: 11px; color: var(--danger, #ef4444);">Advance Deducted (-)</label>
                            <input type="number" step="0.01" id="advance_deduction" name="advance_deduction" class="form-input" value="0.00" oninput="calcNetSalary()">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 11px; color: var(--danger, #ef4444);">Absence / Fine (-)</label>
                            <input type="number" step="0.01" id="penalty_deduction" name="penalty_deduction" class="form-input" value="0.00" oninput="calcNetSalary()">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); margin-top: 1rem; padding-top: 0.75rem;">
                        <span style="font-size: 1rem; font-weight: 800;">Net Salary Payable (মোট প্রদেয়):</span>
                        <strong id="displayNetSalary" style="font-size: 1.35rem; color: var(--success, #10b981);">৳0.00</strong>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Source</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">ক্যাশ ড্রয়ার (Counter Cash)</option>
                        <option value="bank">ব্যাংক একাউন্ট (Bank)</option>
                        <option value="bkash">বিকাশ (bKash)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="salary_date">Disbursement Date</label>
                    <input type="date" id="salary_date" name="salary_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="font-weight: 800;">Disburse Salary & Generate Payslip</button>
            </div>
        </form>
    </div>
</div>

<script>
    function onPayrollStaffSelect(select) {
        const opt = select.options[select.selectedIndex];
        const basic = parseFloat(opt.getAttribute('data-basic')) || 0;
        const advance = parseFloat(opt.getAttribute('data-advance')) || 0;

        document.getElementById('basic_salary').value = basic.toFixed(2);
        document.getElementById('advance_deduction').value = advance.toFixed(2);
        calcNetSalary();
    }

    function calcNetSalary() {
        const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
        const allowance = parseFloat(document.getElementById('allowance').value) || 0;
        const overtime = parseFloat(document.getElementById('overtime_amount').value) || 0;
        const advance = parseFloat(document.getElementById('advance_deduction').value) || 0;
        const penalty = parseFloat(document.getElementById('penalty_deduction').value) || 0;

        const net = Math.max(0, (basic + allowance + overtime) - (advance + penalty));
        document.getElementById('displayNetSalary').textContent = `৳${net.toFixed(2)}`;
    }
</script>
@endsection