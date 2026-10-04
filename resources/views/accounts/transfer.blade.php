@extends('tyro-dashboard::layouts.admin')

@section('title', 'Fund Transfer')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('accounts.index') }}">Accounts</a>
<span class="breadcrumb-separator">/</span>
<span>Transfer</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Fund Transfer (অভ্যন্তরীণ টাকা স্থানান্তর)</h1>
            <p class="page-description">Transfer balance between Cash Drawer, Bank Accounts, and Mobile Wallets.</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">← Back to Accounts</a>
    </div>
</div>

@if(session('error'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--danger, #ef4444); background: rgba(239, 68, 68, 0.08); max-width: 650px; margin-left: auto; margin-right: auto;">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--danger, #ef4444); font-weight: 500; font-size: 0.875rem;">
        {{ session('error') }}
    </div>
</div>
@endif

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('transfers.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="from_account_id">From Account (প্রেরক হিসাব) <span style="color: var(--danger, #ef4444);">*</span></label>
                <select id="from_account_id" name="from_account_id" class="form-select" required>
                    <option value="">নির্বাচন করুন...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ old('from_account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }} (ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('from_account_id') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="to_account_id">To Destination Account (প্রাপক হিসাব) <span style="color: var(--danger, #ef4444);">*</span></label>
                <select id="to_account_id" name="to_account_id" class="form-select" required>
                    <option value="">নির্বাচন করুন...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ old('to_account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }} (ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('to_account_id') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="amount">Transfer Amount (টাকার পরিমাণ ৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" value="{{ old('amount') }}" required style="font-family: monospace; font-weight: 700;">
                    @error('amount') <div style="color: var(--danger, #ef4444); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="transfer_date">Transfer Date (তারিখ) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="transfer_date" name="transfer_date" class="form-input" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="reference">Reference / Deposit Slip No (ঐচ্ছিক)</label>
                <input type="text" id="reference" name="reference" class="form-input" placeholder="e.g. Bank Slip #4092, bKash TrxID" value="{{ old('reference') }}">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Note / Purpose (বিবরণ)</label>
                <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="স্থানান্তরের কারণ...">{{ old('note') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Confirm Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection