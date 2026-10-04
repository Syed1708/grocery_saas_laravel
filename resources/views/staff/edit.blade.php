@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Staff')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('staff.index') }}">Staff</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Staff Profile</h1>
            <p class="page-description">Update staff designation, salary, or branch allocation.</p>
        </div>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('staff.update', ['staff' => $staff->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $staff->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $staff->phone) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="nid_number">NID Card Number</label>
                    <input type="text" id="nid_number" name="nid_number" class="form-input" value="{{ old('nid_number', $staff->nid_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="emergency_phone">Emergency Phone</label>
                    <input type="text" id="emergency_phone" name="emergency_phone" class="form-input" value="{{ old('emergency_phone', $staff->emergency_phone) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="designation">Designation <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="designation" name="designation" class="form-input" value="{{ old('designation', $staff->designation) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-select" required>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ $staff->department_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $staff->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card" style="margin-bottom: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08));">
                <div class="card-body" style="padding: 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 0.75rem;">
                        Salary Structure
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="basic_salary">Basic Salary (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                            <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-input" value="{{ old('basic_salary', $staff->basic_salary) }}" required style="font-weight: 700;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="daily_allowance">Daily Allowance (৳)</label>
                            <input type="number" step="0.01" id="daily_allowance" name="daily_allowance" class="form-input" value="{{ old('daily_allowance', $staff->daily_allowance) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="overtime_rate">Overtime Rate (৳)</label>
                            <input type="number" step="0.01" id="overtime_rate" name="overtime_rate" class="form-input" value="{{ old('overtime_rate', $staff->overtime_rate) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="joining_date">Joining Date</label>
                    <input type="date" id="joining_date" name="joining_date" class="form-input" value="{{ old('joining_date', $staff->joining_date->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" {{ $staff->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $staff->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ $staff->status === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="address">Permanent Address</label>
                <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address', $staff->address) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection