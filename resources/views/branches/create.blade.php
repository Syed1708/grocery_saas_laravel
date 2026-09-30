@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Branch')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('branches.index') }}">Branches</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Store Branch (নতুন শাখা যোগ করুন)</h1>
            <p class="page-description">Create a new branch outlet or sales counter.</p>
        </div>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('branches.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Branch Name (শাখার নাম) <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" placeholder="e.g. ধানমন্ডি শাখা" value="{{ old('name') }}" required>
                @error('name') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="code">Branch Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="code" name="code" class="form-input" value="{{ old('code', 'BR-0'.(\App\Models\Branch::count() + 1)) }}" required style="font-family: monospace;">
                    @error('code') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-input" placeholder="017XXXXXXXX" value="{{ old('phone') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="address">Full Address (ঠিকানা)</label>
                <textarea id="address" name="address" class="form-textarea" rows="2" placeholder="দোকান নং, রোড, এলাকা...">{{ old('address') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; align-items: center;">
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.25rem;">
                    <input type="checkbox" id="is_main" name="is_main" value="1" style="width: 16px; height: 16px;">
                    <label for="is_main" class="form-label" style="margin: 0; cursor: pointer;">This is the Main Branch (মেইন ব্রাঞ্চ)</label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Branch</button>
            </div>
        </form>
    </div>
</div>
@endsection