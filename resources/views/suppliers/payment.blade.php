@extends('tyro-dashboard::layouts.admin')

@section('title', 'Pay Supplier Due')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('suppliers.index') }}">Suppliers</a>
<span class="breadcrumb-separator">/</span>
<span>Payment</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Pay Supplier Due (মহাজন বাকি পরিশোধ)</h1>
            <p class="page-description">Record cash or bank payment against outstanding supplier debt.</p>
        </div>
        <a href="{{ route('suppliers.show', ['supplier' => $supplier->id]) }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body">
        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid var(--danger, #ef4444); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="font-size: 1rem; color: var(--foreground);">{{ $supplier->name }}</strong>
                <div style="font-size: 0.75rem; color: var(--muted-foreground);">{{ $supplier->company_name }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Due</div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--danger, #ef4444);">৳{{ number_format($supplier->current_due, 2) }}</div>
            </div>
        </div>

        <form action="{{ route('suppliers.payment.store', ['supplier' => $supplier->id]) }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="voucher_no">Voucher No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="voucher_no" name="voucher_no" class="form-input" value="{{ old('voucher_no', $voucherNo) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_date">Payment Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="payment_date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="amount">Payment Amount (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" max="{{ $supplier->current_due }}" required style="font-weight: 700;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">ক্যাশ ড্রয়ার (Cash)</option>
                        <option value="bank">ব্যাংক একাউন্ট (Bank)</option>
                        <option value="bkash">বিকাশ (bKash)</option>
                        <option value="nagad">নগদ (Nagad)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="reference">Reference (Cheque No / TrxID)</label>
                <input type="text" id="reference" name="reference" class="form-input" placeholder="e.g. Bank Cheque #918230">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Note</label>
                <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="e.g. বৃহস্পতিবারের সাপ্তাহিক বাকি পরিশোধ"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('suppliers.show', ['supplier' => $supplier->id]) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection