@extends('tyro-dashboard::layouts.admin')

@section('title', 'Record Expense')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('expenses.index') }}">Expenses</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Record Daily Expense (দৈনিক খরচ এন্ট্রি)</h1>
            <p class="page-description">Record store petty cash expenses from counter cash or bank.</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="voucher_no">Voucher No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="voucher_no" name="voucher_no" class="form-input" value="{{ old('voucher_no', $autoVoucher) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="expense_date">Expense Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="expense_date" name="expense_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="category_id">Expense Category <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Amount (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" value="{{ old('amount') }}" required style="font-weight: 700;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Source</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">ক্যাশ ড্রয়ার (Counter Cash)</option>
                        <option value="bkash">বিকাশ (bKash)</option>
                        <option value="nagad">নগদ (Nagad)</option>
                        <option value="bank">ব্যাংক একাউন্ট (Bank)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reference">Reference / Bill No</label>
                    <input type="text" id="reference" name="reference" class="form-input" placeholder="e.g. DESCO Bill #91238">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Expense Details / Note</label>
                <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="e.g. স্টাফদের দুপুরের খাবার ও বিকালের চা-নাস্তা">{{ old('note') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>
@endsection