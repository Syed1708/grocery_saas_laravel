@extends('tyro-dashboard::layouts.admin')

@section('title', 'Supplier Ledger - ' . $supplier->name)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('suppliers.index') }}">Suppliers</a>
<span class="breadcrumb-separator">/</span>
<span>Ledger</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $supplier->name }} (মহাজন খাতা)</h1>
            <p class="page-description">{{ $supplier->company_name ?? 'Distributor' }} • Phone: {{ $supplier->phone }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($supplier->current_due > 0)
                <a href="{{ route('suppliers.payment.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary">
                    Pay Due (বাকি পরিশোধ)
                </a>
            @endif
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">← Back to Suppliers</a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--success, #10b981); background: rgba(16, 185, 129, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--success, #10b981); font-weight: 500; font-size: 0.875rem;">
        {{ session('success') }}
    </div>
</div>
@endif

<!-- Ledger Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Opening Due (প্রারম্ভিক বকেয়া)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--foreground); margin-top: 0.25rem;">৳{{ number_format($supplier->opening_due, 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Invoiced (মোট ক্রয়)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">৳{{ number_format($supplier->purchases()->sum('grand_total'), 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Paid (মোট পরিশোধিত)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--success, #10b981); margin-top: 0.25rem;">৳{{ number_format($supplier->payments()->sum('amount') + $supplier->purchases()->sum('paid_amount'), 2) }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Outstanding Due (বর্তমান বাকি)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($supplier->current_due, 2) }}</div>
        </div>
    </div>
</div>

<!-- Purchase History -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Purchase Chalans (ক্রয় চালান তালিকা)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Chalan No</th>
                    <th>Date</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th style="text-align: right;">Paid</th>
                    <th style="text-align: right;">Due</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                <tr>
                    <td><a href="{{ route('purchases.show', ['purchase' => $p->id]) }}" style="font-family: monospace; font-weight: 700; color: var(--primary);">{{ $p->chalan_no }}</a></td>
                    <td>{{ $p->purchase_date->format('M d, Y') }}</td>
                    <td style="text-align: right; font-weight: 600;">৳{{ number_format($p->grand_total, 2) }}</td>
                    <td style="text-align: right; color: var(--success, #10b981);">৳{{ number_format($p->paid_amount, 2) }}</td>
                    <td style="text-align: right; color: var(--danger, #ef4444); font-weight: 700;">৳{{ number_format($p->due_amount, 2) }}</td>
                    <td style="text-align: center;"><span class="badge {{ $p->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($p->payment_status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--muted-foreground); padding: 1.5rem;">No purchase history found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Repayment History -->
<div class="card">
    <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 0.875rem; font-weight: 700;">Due Payment History (বাকি পরিশোধের বিবরণী)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference / Note</th>
                    <th style="text-align: right;">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pm)
                <tr>
                    <td style="font-family: monospace; font-weight: 600;">{{ $pm->voucher_no }}</td>
                    <td>{{ $pm->payment_date->format('M d, Y') }}</td>
                    <td><span class="badge badge-secondary">{{ strtoupper($pm->payment_method) }}</span></td>
                    <td>{{ $pm->reference ?? $pm->note ?? 'N/A' }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--success, #10b981);">৳{{ number_format($pm->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted-foreground); padding: 1.5rem;">No due payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection