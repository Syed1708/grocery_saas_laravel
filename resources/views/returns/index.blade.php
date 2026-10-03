@extends('tyro-dashboard::layouts.admin')

@section('title', 'Sales Returns')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Sales Returns</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Sales Returns & Replacements (পণ্য ফেরত ও বদল)</h1>
            <p class="page-description">Process item returns against sales invoices and restore stock.</p>
        </div>
        <a href="{{ route('returns.create') }}" class="btn btn-primary">
            + New Return
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
    @if($returns->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Return No</th>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th style="text-align: right;">Refund Amount</th>
                    <th>Refund Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returns as $r)
                <tr>
                    <td><strong style="font-family: monospace;">{{ $r->return_no }}</strong></td>
                    <td><span class="badge badge-secondary">{{ $r->sale?->invoice_no }}</span></td>
                    <td>{{ $r->return_date->format('M d, Y') }}</td>
                    <td>{{ $r->customer?->name ?? 'Cash Customer' }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--danger, #ef4444);">৳{{ number_format($r->total_refund, 2) }}</td>
                    <td><span class="badge badge-primary">{{ strtoupper($r->refund_method) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($returns->hasPages()) <div class="pagination">{{ $returns->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No returns processed</h3>
        <p class="empty-state-description">Returned products can be restocked or marked as damaged loss.</p>
        <a href="{{ route('returns.create') }}" class="btn btn-primary">Process Return</a>
    </div>
    @endif
</div>
@endsection