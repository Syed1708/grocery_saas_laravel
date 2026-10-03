@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Customer')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('customers.index') }}">Customers</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Customer (নতুন কাস্টমার)</h1>
            <p class="page-description">Register regular grocery customers with phone numbers and credit limits.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Customer Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="e.g. মোঃ কামাল হোসেন" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" placeholder="017XXXXXXXX" value="{{ old('phone') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="credit_limit">Credit Limit (সর্বোচ্চ বাকির সীমা ৳)</label>
                    <input type="number" step="0.01" id="credit_limit" name="credit_limit" class="form-input" value="{{ old('credit_limit', '10000.00') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="opening_due">Opening Due (প্রারম্ভিক বকেয়া ৳)</label>
                    <input type="number" step="0.01" id="opening_due" name="opening_due" class="form-input" value="{{ old('opening_due', '0.00') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="address">Address / House No</label>
                <textarea id="address" name="address" class="form-textarea" rows="2" placeholder="বাড়ি নং, রোড, এলাকা...">{{ old('address') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection