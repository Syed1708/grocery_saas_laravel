@extends('tyro-dashboard::layouts.admin')

@section('title', 'Give Advance Salary')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('advances.index') }}">Advance Salary</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Give Advance Salary (অগ্রিম বেতন এন্ট্রি)</h1>
            <p class="page-description">Record mid-month advance cash given to staff.</p>
        </div>
        <a href="{{ route('advances.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('advances.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="staff_id">Select Staff Member <span style="color: var(--danger, #ef4444);">*</span></label>
                <select id="staff_id" name="staff_id" class="form-select" required>
                    <option value="">-- Choose Staff --</option>
                    @foreach($staffMembers as $st)
                        <option value="{{ $st->id }}">
                            {{ $st->name }} ({{ $st->designation }}) - Base: ৳{{ number_format($st->basic_salary, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="advance_date">Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="advance_date" name="advance_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Advance Amount (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="2000.00" required style="font-weight: 700;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="payment_method">Payment Source</label>
                <select id="payment_method" name="payment_method" class="form-select">
                    <option value="cash">ক্যাশ কাউন্টার (Counter Cash)</option>
                    <option value="bkash">বিকাশ (bKash)</option>
                    <option value="nagad">নগদ (Nagad)</option>
                    <option value="bank">ব্যাংক একাউন্ট (Bank)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="purpose">Reason / Purpose</label>
                <textarea id="purpose" name="purpose" class="form-textarea" rows="2" placeholder="e.g. পরিবারের চিকিৎসা খরচ বাবদ অগ্রিম"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('advances.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Advance</button>
            </div>
        </form>
    </div>
</div>
@endsection