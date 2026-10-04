@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Other Income')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('incomes.index') }}">Other Incomes</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Other Income (আয়ের তথ্য সংশোধন)</h1>
            <p class="page-description">Update income details or amount.</p>
        </div>
        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('incomes.update', ['income' => $income->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="receipt_no">Receipt No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="receipt_no" name="receipt_no" class="form-input" value="{{ old('receipt_no', $income->receipt_no) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="income_date">Income Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="income_date" name="income_date" class="form-input" value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="income_source">Income Source <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="income_source" name="income_source" class="form-input" value="{{ old('income_source', $income->income_source) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Amount Received (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" value="{{ old('amount', $income->amount) }}" required style="font-weight: 700; color: var(--success, #10b981);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Received Via</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash" {{ $income->payment_method === 'cash' ? 'selected' : '' }}>ক্যাশ (Cash)</option>
                        <option value="bkash" {{ $income->payment_method === 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                        <option value="nagad" {{ $income->payment_method === 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                        <option value="bank" {{ $income->payment_method === 'bank' ? 'selected' : '' }}>ব্যাংক (Bank)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reference">Reference</label>
                    <input type="text" id="reference" name="reference" class="form-input" value="{{ old('reference', $income->reference) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Details</label>
                <textarea id="note" name="note" class="form-textarea" rows="2">{{ old('note', $income->note) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('incomes.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Income</button>
            </div>
        </form>
    </div>
</div>
@endsection