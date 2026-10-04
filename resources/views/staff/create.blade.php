@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Staff')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('staff.index') }}">Staff</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Staff Profile (নতুন কর্মচারী)</h1>
            <p class="page-description">Record personal details, designation, branch, and salary structure.</p>
        </div>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('staff.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="e.g. মোঃ তারেক মাহমুদ" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" placeholder="017XXXXXXXX" value="{{ old('phone') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="nid_number">NID Card Number</label>
                    <input type="text" id="nid_number" name="nid_number" class="form-input" placeholder="National ID" value="{{ old('nid_number') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="emergency_phone">Emergency Contact Phone</label>
                    <input type="text" id="emergency_phone" name="emergency_phone" class="form-input" placeholder="Guardian / Relative" value="{{ old('emergency_phone') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="designation">Designation <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="designation" name="designation" class="form-input" placeholder="e.g. সেলসম্যান / ক্যাশিয়ার" value="{{ old('designation') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department_id">Department <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="department_id" name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="branch_id">Branch <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="branch_id" name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Salary Structure Card -->
            <div class="card" style="margin-bottom: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08));">
                <div class="card-body" style="padding: 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 0.75rem;">
                        Salary Structure (বেতন কাঠামো)
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="basic_salary">Basic Monthly Salary (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                            <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-input" placeholder="15000.00" value="{{ old('basic_salary') }}" required style="font-weight: 700;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="daily_allowance">Daily Food Allowance (৳)</label>
                            <input type="number" step="0.01" id="daily_allowance" name="daily_allowance" class="form-input" placeholder="100.00" value="{{ old('daily_allowance', '0') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="overtime_rate">Overtime Hourly Rate (৳)</label>
                            <input type="number" step="0.01" id="overtime_rate" name="overtime_rate" class="form-input" placeholder="100.00" value="{{ old('overtime_rate', '0') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="joining_date">Joining Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="joining_date" name="joining_date" class="form-input" value="{{ old('joining_date', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active (সক্রিয়)</option>
                        <option value="inactive">Inactive</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="address">Permanent Address</label>
                <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Staff Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection