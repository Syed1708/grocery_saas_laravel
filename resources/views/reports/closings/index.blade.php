@extends('tyro-dashboard::layouts.admin')

@section('title', 'Day-End Closings (Z-Reports)')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Z-Reports</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Day-End Z-Reports (কাউন্টার ক্যাশ ক্লোজিং রেকর্ড)</h1>
            <p class="page-description">Daily cashier shift closings, physical drawer cash counts, and variance audits.</p>
        </div>
        <a href="{{ route('closings.create') }}" class="btn btn-primary">
            ➕ Perform Day-End Closing (আজকের ক্লোজিং)
        </a>
    </div>
</div>

@if(session('success'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--success, #10b981); background: rgba(16, 185, 129, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--success, #10b981); font-weight: 500; font-size: 0.875rem;">
        {{ session('success') }}
    </div>
</div>
@endif

<div class="card">
    @if($closings->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Closed By</th>
                    <th style="text-align: right;">Cash Sales</th>
                    <th style="text-align: right;">Cash In Total</th>
                    <th style="text-align: right;">Cash Out Total</th>
                    <th style="text-align: right;">Expected Cash</th>
                    <th style="text-align: right;">Counted Cash</th>
                    <th style="text-align: right;">Variance (পার্থক্য)</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($closings as $c)
                <tr>
                    <td style="font-family: monospace; font-weight: 600;">
                        {{ $c->closing_date->format('d M, Y') }}
                    </td>
                    <td>{{ $c->user?->name ?? 'Cashier' }}</td>
                    <td style="text-align: right; font-family: monospace;">৳{{ number_format($c->cash_sales, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--success, #10b981);">
                        ৳{{ number_format($c->opening_cash + $c->cash_sales + $c->cash_due_collected + $c->cash_other_income, 2) }}
                    </td>
                    <td style="text-align: right; font-family: monospace; color: var(--danger, #ef4444);">
                        ৳{{ number_format($c->cash_expenses + $c->cash_supplier_paid + $c->cash_refunds, 2) }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($c->expected_cash, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0284c7;">৳{{ number_format($c->counted_cash, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 800; color: {{ $c->difference == 0 ? 'var(--success, #10b981)' : ($c->difference > 0 ? '#0284c7' : 'var(--danger, #ef4444)') }};">
                        {{ $c->difference >= 0 ? '+' : '' }}৳{{ number_format($c->difference, 2) }}
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('closings.show', $c) }}" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                            Z-Slip 🖨️
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($closings->hasPages())
    <div class="pagination">
        {{ $closings->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No day-end closings recorded yet</h3>
        <p class="empty-state-description">Perform day-end closings to reconcile physical drawer cash with daily transactions.</p>
        <a href="{{ route('closings.create') }}" class="btn btn-primary">Perform Day-End Closing</a>
    </div>
    @endif
</div>
@endsection