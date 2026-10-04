@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Account')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('accounts.index') }}">Accounts</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Account / Wallet (নতুন হিসাব বা ওয়ালেট)</h1>
            <p class="page-description">Register a cash drawer, bank account, or mobile wallet (bKash/Nagad).</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Account Name (হিসাবের নাম) <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" placeholder="e.g. ক্যাশ কাউন্টার ড্রয়ার, ইসলামী ব্যাংক চলতি হিসাব" value="{{ old('name') }}" required>
                @error('name') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="type">Account Type (অ্যাকাউন্টের ধরন) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Cash Drawer (ক্যাশ ড্রয়ার)</option>
                        <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Bank Account (ব্যাংক হিসাব)</option>
                        <option value="bkash" {{ old('type') == 'bkash' ? 'selected' : '' }}>bKash (মার্চেন্ট / পার্সোনাল)</option>
                        <option value="nagad" {{ old('type') == 'nagad' ? 'selected' : '' }}>Nagad (নগদ ওয়ালেট)</option>
                        <option value="rocket" {{ old('type') == 'rocket' ? 'selected' : '' }}>Rocket (রকেট ওয়ালেট)</option>
                        <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other (অন্যান্য)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="opening_balance">Opening Balance (প্রারম্ভিক ব্যালেন্স ৳)</label>
                    <input type="number" step="0.01" id="opening_balance" name="opening_balance" class="form-input" placeholder="0.00" value="{{ old('opening_balance', '0.00') }}" style="font-family: monospace;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="account_number">Account / Wallet Number</label>
                    <input type="text" id="account_number" name="account_number" class="form-input" placeholder="e.g. 205012345678901 / 017XXXXXXXX" value="{{ old('account_number') }}" style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="bank_name">Bank Name (if applicable)</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-input" placeholder="e.g. Islami Bank, BRAC Bank" value="{{ old('bank_name') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="branch_name">Bank Branch Name</label>
                    <input type="text" id="branch_name" name="branch_name" class="form-input" placeholder="e.g. কাওরান বাজার শাখা" value="{{ old('branch_name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="notes">Notes / Description (মন্তব্য)</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2" placeholder="অ্যাকাউন্ট সম্পর্কিত অতিরিক্ত তথ্য...">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Account</button>
            </div>
        </form>
    </div>
</div>
@endsection