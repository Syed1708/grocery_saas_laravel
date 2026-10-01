@extends('tyro-dashboard::layouts.admin')

@section('title', 'Purchase Invoices')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Purchases</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Purchase Invoices (স্টক ক্রয় ও চালান তালিকা)</h1>
            <p class="page-description">Procurement history, supplier invoices, freight charges, and payments.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Purchase (পণ্য ক্রয়)
        </a>
    </div>
</div>

<!-- Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Purchases</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">৳{{ number_format($totalPurchases, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📦</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Supplier Due Balance</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($totalDue, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">⏳</div>
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
    @if($purchases->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Chalan No</th>
                    <th>Date</th>
                    <th>Supplier / Distributor</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th style="text-align: right;">Paid</th>
                    <th style="text-align: right;">Due</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $p)
                <tr>
                    <td>
                        <a href="{{ route('purchases.show', ['purchase' => $p->id]) }}" style="font-family: monospace; font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $p->chalan_no }}
                        </a>
                    </td>
                    <td>{{ $p->purchase_date->format('M d, Y') }}</td>
                    <td>{{ $p->supplier?->name ?? 'N/A' }}</td>
                    <td style="text-align: right; font-weight: 600;">৳{{ number_format($p->grand_total, 2) }}</td>
                    <td style="text-align: right; color: var(--success, #10b981);">৳{{ number_format($p->paid_amount, 2) }}</td>
                    <td style="text-align: right; color: var(--danger, #ef4444); font-weight: 700;">৳{{ number_format($p->due_amount, 2) }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $p->payment_status === 'paid' ? 'badge-success' : ($p->payment_status === 'partial' ? 'badge-warning' : 'badge-danger') }}">
                            {{ ucfirst($p->payment_status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('purchases.show', ['purchase' => $p->id]) }}" class="action-btn" title="View Chalan Voucher">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('purchases.edit', ['purchase' => $p->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('purchases.destroy', ['purchase' => $p->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Cancel this chalan? It will reverse added inventory.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Cancel Chalan">
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
    @if($purchases->hasPages()) <div class="pagination">{{ $purchases->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No purchases recorded</h3>
        <p class="empty-state-description">Record wholesale stock purchases to replenish your branch inventory.</p>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">New Purchase</a>
    </div>
    @endif
</div>
@endsection