@extends('tyro-dashboard::layouts.admin')

@section('title', 'Loans & Hawlat')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Banking & Accounts</span>
<span class="breadcrumb-separator">/</span>
<span>Loans & Hawlat</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Loans & Hawlat (ঋণ ও হাওলাত লেজার)</h1>
            <p class="page-description">Track store loans taken and personal hawlat given with installment repayments.</p>
        </div>
        <a href="{{ route('loans.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Loan / Hawlat
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

@if(session('error'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--danger, #ef4444); background: rgba(239, 68, 68, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--danger, #ef4444); font-weight: 500; font-size: 0.875rem;">
        {{ session('error') }}
    </div>
</div>
@endif

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">দোকানের বকেয়া ঋণ (Loans Taken - Payable)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--danger, #ef4444); font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($totalTaken, 2) }}
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">অন্যকে দেওয়া পাওনা হাওলাত (Loans Given - Receivable)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--success, #10b981); font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($totalGiven, 2) }}
            </span>
        </div>
    </div>
</div>

<div class="card">
    @if($loans->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Person / Lender</th>
                    <th>Date</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th style="text-align: right;">Paid / Repaid</th>
                    <th style="text-align: right;">Remaining Due</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loans as $loan)
                <tr>
                    <td>
                        <span class="badge {{ $loan->type === 'taken' ? 'badge-danger' : 'badge-success' }}" style="font-size: 10px;">
                            {{ $loan->type === 'taken' ? '📥 ঋণ গ্রহণ' : '📤 ধার প্রদান' }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: var(--foreground); font-size: 0.875rem;">{{ $loan->person_name }}</strong>
                        @if($loan->phone)
                            <div style="font-family: monospace; font-size: 0.75rem; color: var(--muted-foreground);">{{ $loan->phone }}</div>
                        @endif
                    </td>
                    <td style="font-family: monospace; color: var(--muted-foreground);">
                        {{ $loan->loan_date->format('d M, Y') }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--foreground);">
                        ৳{{ number_format($loan->amount, 2) }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">
                        ৳{{ number_format($loan->total_paid, 2) }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 800; color: var(--danger, #ef4444);">
                        ৳{{ number_format($loan->remaining_amount, 2) }}
                    </td>
                    <td style="text-align: center;">
                        @if($loan->status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @else
                            <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Active</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('loans.show', ['loan' => $loan->id]) }}" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                            কিস্তি ও বিবরণী →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($loans->hasPages())
    <div class="pagination">
        {{ $loans->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No loans recorded</h3>
        <p class="empty-state-description">Record loans taken for business expansion or informal hawlat given to others.</p>
        <a href="{{ route('loans.create') }}" class="btn btn-primary">Add Loan / Hawlat</a>
    </div>
    @endif
</div>
@endsection