@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Expense')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('expenses.index') }}">Expenses</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Expense (খরচের তথ্য সংশোধন)</h1>
            <p class="page-description">Modify expense category, amount, or voucher notes.</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('expenses.update', ['expense' => $expense->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="voucher_no">Voucher No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="voucher_no" name="voucher_no" class="form-input" value="{{ old('voucher_no', $expense->voucher_no) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="expense_date">Expense Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="expense_date" name="expense_date" class="form-input" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="category_id">Expense Category <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ $expense->category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Amount (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" value="{{ old('amount', $expense->amount) }}" required style="font-weight: 700;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Source</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash" {{ $expense->payment_method === 'cash' ? 'selected' : '' }}>ক্যাশ ড্রয়ার (Cash)</option>
                        <option value="bkash" {{ $expense->payment_method === 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                        <option value="nagad" {{ $expense->payment_method === 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                        <option value="bank" {{ $expense->payment_method === 'bank' ? 'selected' : '' }}>ব্যাংক (Bank)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reference">Reference / Bill No</label>
                    <input type="text" id="reference" name="reference" class="form-input" value="{{ old('reference', $expense->reference) }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Expense Details</label>
                <textarea id="note" name="note" class="form-textarea" rows="2">{{ old('note', $expense->note) }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Expense</button>
            </div>
        </form>
    </div>
</div>
@endsection