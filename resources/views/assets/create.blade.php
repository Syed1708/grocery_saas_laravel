@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Asset')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('assets.index') }}">Assets</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Company Asset</h1>
            <p class="page-description">Record store equipment, refrigerators, or electronics for net worth tracking.</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('assets.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Asset Name <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Walton 300L Deep Freezer" value="{{ old('name') }}" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="asset_code">Asset Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="asset_code" name="asset_code" class="form-input" value="{{ old('asset_code', $nextCode) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="serial_number">Serial Number / Model</label>
                    <input type="text" id="serial_number" name="serial_number" class="form-input" placeholder="e.g. WDF-300L-2026" value="{{ old('serial_number') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="purchase_date">Purchase Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="condition">Condition</label>
                    <select id="condition" name="condition" class="form-select">
                        <option value="good">Good</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="damaged">Damaged</option>
                        <option value="disposed">Disposed</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="purchase_cost">Original Cost (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="purchase_cost" name="purchase_cost" class="form-input" placeholder="52000.00" value="{{ old('purchase_cost') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="current_value">Current Valuation (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="current_value" name="current_value" class="form-input" placeholder="47000.00" value="{{ old('current_value') }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="notes">Warranty & Service Notes</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2" placeholder="e.g. 10-year compressor warranty card inside cash drawer">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('assets.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection