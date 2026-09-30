@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Measurement Unit')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('units.index') }}">Units</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Measurement Unit</h1>
            <p class="page-description">Create a new base or fractional unit for product stock.</p>
        </div>
        <a href="{{ route('units.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('units.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Unit Name <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" placeholder="e.g. কেজি (Kilogram), বস্তা (Sack)" value="{{ old('name') }}" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="short_code">Short Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="short_code" name="short_code" class="form-input" placeholder="e.g. kg, pcs, sack" value="{{ old('short_code') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="card" style="margin-bottom: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08));">
                <div class="card-body" style="padding: 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 0.75rem;">
                        Sub-Unit Conversion (Optional)
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="base_unit_id">Base Unit</label>
                            <select id="base_unit_id" name="base_unit_id" class="form-select">
                                <option value="">-- Main Unit (None) --</option>
                                @foreach($baseUnits as $bu)
                                    <option value="{{ $bu->id }}">{{ $bu->name }} ({{ $bu->short_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="conversion_rate">Conversion Rate</label>
                            <input type="number" step="0.0001" id="conversion_rate" name="conversion_rate" class="form-input" placeholder="e.g. 50 (1 Sack = 50 Kg)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="allow_decimal" name="allow_decimal" value="1" checked style="width: 16px; height: 16px;">
                <label for="allow_decimal" class="form-label" style="margin: 0; cursor: pointer;">Allow Decimal Quantities (e.g. 1.250 Kg)</label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('units.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Unit</button>
            </div>
        </form>
    </div>
</div>
@endsection