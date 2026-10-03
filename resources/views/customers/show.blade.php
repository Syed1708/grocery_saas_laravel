@extends('tyro-dashboard::layouts.admin')

@section('title', 'Bakir Khata - ' . $customer->name)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('customers.index') }}">Customers</a>
<span class="breadcrumb-separator">/</span>
<span>Bakir Khata</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $customer->name }} (বাকি খাতা)</h1>
            <p class="page-description">Phone: {{ $customer->phone }} • Address: {{ $customer->address ?? 'N/A' }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($customer->current_due > 0)
                <a href="{{ route('customers.payment.create', ['customer' => $customer->id]) }}" class="btn btn-primary">
                    Collect Due (বাকি আদায়)
                </a>
            @endif
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Credit Limit (সর্বোচ্চ সীমা)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--foreground); margin-top: 0.25rem;">৳{{ number_format($customer->credit_limit, 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Invoiced Sales</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">৳{{ number_format($customer->sales()->sum('grand_total'), 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Paid (মোট পরিশোধিত)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--success, #10b981); margin-top: 0.25rem;">৳{{ number_format($customer->payments()->sum('amount') + $customer->sales()->sum('paid_amount'), 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Current Due (মোট বকেয়া)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($customer->current_due, 2) }}</div>
        </div>
    </div>
</div>

<!-- Sales Invoices History -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Purchase History (মেমো তালিকা)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th style="text-align: right;">Paid</th>
                    <th style="text-align: right;">Due</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td><a href="{{ route('sales.show', ['sale' => $s->id]) }}" style="font-family: monospace; font-weight: 700; color: var(--primary);">{{ $s->invoice_no }}</a></td>
                    <td>{{ $s->sale_date->format('M d, Y') }}</td>
                    <td style="text-align: right; font-weight: 600;">৳{{ number_format($s->grand_total, 2) }}</td>
                    <td style="text-align: right; color: var(--success, #10b981);">৳{{ number_format($s->paid_amount, 2) }}</td>
                    <td style="text-align: right; color: var(--danger, #ef4444); font-weight: 700;">৳{{ number_format($s->due_amount, 2) }}</td>
                    <td style="text-align: center;"><span class="badge {{ $s->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($s->payment_status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--muted-foreground); padding: 1.5rem;">No sales history found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Due Collections History -->
<div class="card">
    <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Due Collection Receipts (বাকি আদায়ের হিসেব)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Reference / Note</th>
                    <th style="text-align: right;">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pm)
                <tr>
                    <td style="font-family: monospace; font-weight: 600;">{{ $pm->receipt_no }}</td>
                    <td>{{ $pm->payment_date->format('M d, Y') }}</td>
                    <td><span class="badge badge-secondary">{{ strtoupper($pm->payment_method) }}</span></td>
                    <td>{{ $pm->reference ?? $pm->note ?? 'N/A' }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--success, #10b981);">৳{{ number_format($pm->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted-foreground); padding: 1.5rem;">No due payments collected yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection