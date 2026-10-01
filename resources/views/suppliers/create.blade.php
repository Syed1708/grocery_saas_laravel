@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Supplier')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('suppliers.index') }}">Suppliers</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Supplier / Mahajon (নতুন মহাজন)</h1>
            <p class="page-description">Record distributor company, contact person, and opening due balance.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Supplier / Agency Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="e.g. হাজী এন্টারপ্রাইজ" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="company_name">Distributor Brand / Group</label>
                    <input type="text" id="company_name" name="company_name" class="form-input" placeholder="e.g. প্রাণ ডিলার / আকিজ গ্রুপ" value="{{ old('company_name') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" placeholder="017XXXXXXXX" value="{{ old('phone') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_person">SR / Delivery Man Name</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-input" placeholder="e.g. মোঃ মামুন" value="{{ old('contact_person') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="address">Address / Office Location</label>
                <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="opening_due">Opening Due Balance (৳)</label>
                    <input type="number" step="0.01" id="opening_due" name="opening_due" class="form-input" placeholder="0.00" value="{{ old('opening_due', '0') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>
@endsection