@extends('tyro-dashboard::layouts.admin')

@section('title', 'New Cheque Entry')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('cheques.index') }}">Cheques</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">New Cheque Entry (নতুন চেক এন্ট্রি)</h1>
            <p class="page-description">Record a received or issued bank cheque for future clearance.</p>
        </div>
        <a href="{{ route('cheques.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('cheques.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="type">Cheque Type (চেকের ধরন) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="received" {{ old('type') == 'received' ? 'selected' : '' }}>Received (কাস্টমারের দেওয়া চেক)</option>
                        <option value="issued" {{ old('type') == 'issued' ? 'selected' : '' }}>Issued (মহাজনকে দেওয়া চেক)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="party_name">Party Name (ব্যক্তি / প্রতিষ্ঠান) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="party_name" name="party_name" class="form-input" placeholder="e.g. হাজী এন্টারপ্রাইজ / রহিম ট্রেডার্স" value="{{ old('party_name') }}" required>
                </div>
            </div>

            <input type="hidden" name="party_type" value="other">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="bank_name">Bank Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="bank_name" name="bank_name" class="form-input" placeholder="e.g. Islami Bank, City Bank" value="{{ old('bank_name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="cheque_number">Cheque Number <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="cheque_number" name="cheque_number" class="form-input" placeholder="e.g. CHQ-990182" value="{{ old('cheque_number') }}" required style="font-family: monospace;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="amount">Amount (টাকার পরিমাণ ৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" value="{{ old('amount') }}" required style="font-family: monospace; font-weight: 700;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cheque_date">Cheque Date (চেকের তারিখ) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="cheque_date" name="cheque_date" class="form-input" value="{{ old('cheque_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Note / Memo</label>
                <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="চেকের বিবরণ বা রেফারেন্স...">{{ old('note') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('cheques.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Cheque</button>
            </div>
        </form>
    </div>
</div>
@endsection