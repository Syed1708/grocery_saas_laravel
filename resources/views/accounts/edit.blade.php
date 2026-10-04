@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Account')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('accounts.index') }}">Accounts</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Account (অ্যাকাউন্ট তথ্য সংশোধন)</h1>
            <p class="page-description">Update account information, bank details, and status.</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('accounts.update', ['account' => $account->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Account Name (হিসাবের নাম) <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $account->name) }}" required>
                @error('name') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="type">Account Type <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="cash" {{ $account->type == 'cash' ? 'selected' : '' }}>Cash Drawer</option>
                        <option value="bank" {{ $account->type == 'bank' ? 'selected' : '' }}>Bank Account</option>
                        <option value="bkash" {{ $account->type == 'bkash' ? 'selected' : '' }}>bKash</option>
                        <option value="nagad" {{ $account->type == 'nagad' ? 'selected' : '' }}>Nagad</option>
                        <option value="rocket" {{ $account->type == 'rocket' ? 'selected' : '' }}>Rocket</option>
                        <option value="other" {{ $account->type == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="account_number">Account / Phone Number</label>
                    <input type="text" id="account_number" name="account_number" class="form-input" value="{{ old('account_number', $account->account_number) }}" style="font-family: monospace;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-input" value="{{ old('bank_name', $account->bank_name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="branch_name">Bank Branch Name</label>
                    <input type="text" id="branch_name" name="branch_name" class="form-input" value="{{ old('branch_name', $account->branch_name) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="active" {{ $account->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $account->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2">{{ old('notes', $account->notes) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</div>
@endsection