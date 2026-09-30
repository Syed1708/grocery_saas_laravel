@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Brand')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('brands.index') }}">Brands</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Brand / Company</h1>
            <p class="page-description">Update manufacturer and contact information.</p>
        </div>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('brands.update', ['brand' => $brand->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Brand Name <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $brand->name) }}" required>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="company_name">Company / Parent Group</label>
                <input type="text" id="company_name" name="company_name" class="form-input" value="{{ old('company_name', $brand->company_name) }}">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="contact_person">Distributor SR / Contact</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-input" value="{{ old('contact_person', $brand->contact_person) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $brand->phone) }}">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="active" {{ $brand->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $brand->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Brand</button>
            </div>
        </form>
    </div>
</div>
@endsection