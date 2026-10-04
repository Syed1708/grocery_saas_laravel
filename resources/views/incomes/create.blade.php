@extends('tyro-dashboard::layouts.admin')

@section('title', 'Record Other Income')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('incomes.index') }}">Other Incomes</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Record Other Income (অন্যান্য আয় এন্ট্রি)</h1>
            <p class="page-description">Record non-sale revenue from empty cartons, sacks, and commissions.</p>
        </div>
        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('incomes.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="receipt_no">Receipt No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="receipt_no" name="receipt_no" class="form-input" value="{{ old('receipt_no', $autoReceipt) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="income_date">Income Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="income_date" name="income_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="income_source">Income Source / Category <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="income_source" name="income_source" class="form-input" placeholder="e.g. খালি তেলের টিন ও কার্টুন বিক্রি / বিকাশ ক্যাশআউট কমিশন" value="{{ old('income_source') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Amount Received (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" value="{{ old('amount') }}" required style="font-weight: 700; color: var(--success, #10b981);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Received Via</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">ক্যাশ কাউন্টার (Cash)</option>
                        <option value="bkash">বিকাশ (bKash)</option>
                        <option value="nagad">নগদ (Nagad)</option>
                        <option value="bank">ব্যাংক একাউন্ট (Bank)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reference">Reference</label>
                    <input type="text" id="reference" name="reference" class="form-input" placeholder="e.g. TrxID / মেমো নং">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Details / Description</label>
                <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="e.g. ৫০টি চালের খালি বস্তা বিক্রি বাবদ নগদ গ্রহণ">{{ old('note') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('incomes.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Income</button>
            </div>
        </form>
    </div>
</div>
@endsection