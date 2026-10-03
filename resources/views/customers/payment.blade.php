@extends('tyro-dashboard::layouts.admin')

@section('title', 'Collect Due')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('customers.index') }}">Customers</a>
<span class="breadcrumb-separator">/</span>
<span>Collect Due</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Collect Customer Due (বাকি আদায় মেমো)</h1>
            <p class="page-description">Record cash or bKash due repayment from customer.</p>
        </div>
        <a href="{{ route('customers.show', ['customer' => $customer->id]) }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body">
        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid var(--danger, #ef4444); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="font-size: 1rem; color: var(--foreground);">{{ $customer->name }}</strong>
                <div style="font-size: 0.75rem; color: var(--muted-foreground);">Phone: {{ $customer->phone }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Outstanding Due</div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--danger, #ef4444);">৳{{ number_format($customer->current_due, 2) }}</div>
            </div>
        </div>

        <form action="{{ route('customers.payment.store', ['customer' => $customer->id]) }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="receipt_no">Receipt No <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="receipt_no" name="receipt_no" class="form-input" value="{{ old('receipt_no', $receiptNo) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_date">Date <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="payment_date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="amount">Collection Amount (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" max="{{ $customer->current_due }}" required style="font-weight: 700;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">ক্যাশ কাউন্টার (Cash)</option>
                        <option value="bkash">বিকাশ (bKash)</option>
                        <option value="nagad">নগদ (Nagad)</option>
                        <option value="bank">ব্যাংক একাউন্ট (Bank)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="reference">Reference / TrxID</label>
                <input type="text" id="reference" name="reference" class="form-input" placeholder="e.g. bKash TrxID #BL98231">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="note">Note</label>
                <textarea id="note" name="note" class="form-textarea" rows="2" placeholder="e.g. মাসিক মুদি বাকি পরিশোধ"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('customers.show', ['customer' => $customer->id]) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Confirm Receipt</button>
            </div>
        </form>
    </div>
</div>
@endsection