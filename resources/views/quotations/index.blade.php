@extends('tyro-dashboard::layouts.admin')

@section('title', 'Quotations')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Quotations</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Quotations & Estimates (দরপত্র / মেমো)</h1>
            <p class="page-description">Generate price estimations and convert them to invoices in 1 click.</p>
        </div>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary">
            + Create Quotation
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
    @if($quotations->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Quotation No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th style="text-align: right;">Estimated Total</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotations as $q)
                <tr>
                    <td><strong style="font-family: monospace; color: var(--primary);">{{ $q->quotation_no }}</strong></td>
                    <td>{{ $q->quotation_date->format('M d, Y') }}</td>
                    <td>{{ $q->customer?->name ?? 'General Estimate' }}</td>
                    <td style="text-align: right; font-weight: 700;">৳{{ number_format($q->grand_total, 2) }}</td>
                    <td style="text-align: center;"><span class="badge badge-secondary">{{ ucfirst($q->status) }}</span></td>
                    <td style="text-align: right;">
                        <a href="{{ route('quotations.show', ['quotation' => $q->id]) }}" class="btn btn-secondary" style="padding: 2px 8px; font-size: 11px;">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($quotations->hasPages()) <div class="pagination">{{ $quotations->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No quotations created</h3>
        <p class="empty-state-description">Generate estimates for wedding orders, catering, or bulk corporate buyers.</p>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary">Create Quotation</a>
    </div>
    @endif
</div>
@endsection