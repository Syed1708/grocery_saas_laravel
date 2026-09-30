@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Asset')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('assets.index') }}">Assets</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Company Asset (সম্পদের তথ্য পরিবর্তন)</h1>
            <p class="page-description">Update equipment condition, valuation, and branch allocation.</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('assets.update', ['asset' => $asset->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Asset Name <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $asset->name) }}" required>
                @error('name') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="asset_code">Asset Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="asset_code" name="asset_code" class="form-input" value="{{ old('asset_code', $asset->asset_code) }}" required>
                    @error('asset_code') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="serial_number">Serial Number / Model</label>
                    <input type="text" id="serial_number" name="serial_number" class="form-input" value="{{ old('serial_number', $asset->serial_number) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id" class="form-select">
                        <option value="">All Branches (গ্লোবাল)</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $asset->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="purchase_date">Purchase Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-input" value="{{ old('purchase_date', $asset->purchase_date->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="condition">Condition</label>
                    <select id="condition" name="condition" class="form-select">
                        <option value="good" {{ $asset->condition === 'good' ? 'selected' : '' }}>Good (ভালো)</option>
                        <option value="maintenance" {{ $asset->condition === 'maintenance' ? 'selected' : '' }}>Servicing (মেরামত চলছে)</option>
                        <option value="damaged" {{ $asset->condition === 'damaged' ? 'selected' : '' }}>Damaged (নষ্ট)</option>
                        <option value="disposed" {{ $asset->condition === 'disposed' ? 'selected' : '' }}>Disposed (বাতিল)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="purchase_cost">Original Cost (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="purchase_cost" name="purchase_cost" class="form-input" value="{{ old('purchase_cost', $asset->purchase_cost) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="current_value">Current Valuation (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="current_value" name="current_value" class="form-input" value="{{ old('current_value', $asset->current_value) }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="notes">Warranty & Service Notes</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2">{{ old('notes', $asset->notes) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('assets.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection