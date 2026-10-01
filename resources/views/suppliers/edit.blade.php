@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Supplier')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('suppliers.index') }}">Suppliers</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Supplier / Mahajon (মহাজন তথ্য পরিবর্তন)</h1>
            <p class="page-description">Update supplier contact info, distributor company, and agency details.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">← Back to Suppliers</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('suppliers.update', ['supplier' => $supplier->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Supplier / Agency Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $supplier->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="company_name">Distributor Group</label>
                    <input type="text" id="company_name" name="company_name" class="form-input" value="{{ old('company_name', $supplier->company_name) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $supplier->phone) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_person">SR / Delivery Person</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-input" value="{{ old('contact_person', $supplier->contact_person) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="address">Address / Office Location</label>
                <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $supplier->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" {{ $supplier->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $supplier->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Supplier</button>
            </div>
        </form>
    </div>
</div>
@endsection