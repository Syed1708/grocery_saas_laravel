@extends('tyro-dashboard::layouts.admin')

@section('title', 'Other Incomes')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Other Incomes</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Other Non-Sale Incomes (অন্যান্য আয়)</h1>
            <p class="page-description">Record income from empty cartons/sacks, scrap sales, and bKash cash-out commissions.</p>
        </div>
        <a href="{{ route('incomes.create') }}" class="btn btn-primary">
            + Record Other Income
        </a>
    </div>
</div>

<!-- Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Other Income This Month</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success, #10b981); margin-top: 0.25rem;">৳{{ number_format($totalIncomeThisMonth, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--success, #10b981); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">💵</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Overall Other Income</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--foreground); margin-top: 0.25rem;">৳{{ number_format($totalIncomeAll, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📈</div>
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

<div class="card">
    @if($incomes->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Date</th>
                    <th>Income Source (খাত)</th>
                    <th>Received By</th>
                    <th>Method</th>
                    <th>Details / Note</th>
                    <th style="text-align: right;">Amount (৳)</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomes as $inc)
                <tr>
                    <td><strong style="font-family: monospace;">{{ $inc->receipt_no }}</strong></td>
                    <td>{{ $inc->income_date->format('M d, Y') }}</td>
                    <td><strong style="color: var(--primary);">{{ $inc->income_source }}</strong></td>
                    <td>{{ $inc->user?->name ?? 'Staff' }}</td>
                    <td><span class="badge badge-primary">{{ strtoupper($inc->payment_method) }}</span></td>
                    <td style="color: var(--muted-foreground);">{{ $inc->note ?? $inc->reference ?? 'N/A' }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--success, #10b981);">
                        +৳{{ number_format($inc->amount, 2) }}
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('incomes.edit', ['income' => $inc->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('incomes.destroy', ['income' => $inc->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this income record?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($incomes->hasPages()) <div class="pagination">{{ $incomes->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No other incomes recorded</h3>
        <p class="empty-state-description">Record carton sales, empty rice sack sales, or MFS commissions.</p>
        <a href="{{ route('incomes.create') }}" class="btn btn-primary">Record Income</a>
    </div>
    @endif
</div>
@endsection