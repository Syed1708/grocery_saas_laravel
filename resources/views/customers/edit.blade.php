@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Customer')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('customers.index') }}">Customers</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Customer Information</h1>
            <p class="page-description">Update credit limit, mobile phone, or address.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('customers.update', ['customer' => $customer->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Customer Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $customer->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $customer->phone) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="credit_limit">Credit Limit (৳)</label>
                    <input type="number" step="0.01" id="credit_limit" name="credit_limit" class="form-input" value="{{ old('credit_limit', $customer->credit_limit) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="address">Address</label>
                <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection