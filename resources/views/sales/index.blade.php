@extends('tyro-dashboard::layouts.admin')

@section('title', 'Sales Invoices')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Sales</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Sales Invoices (বিক্রয় মেমো তালিকা)</h1>
            <p class="page-description">Browse retail transactions, cashier receipts, and customer dues.</p>
        </div>
        <a href="{{ route('pos.index') }}" target="_blank" class="btn btn-primary">
            🛒 Open POS Register
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Retail Sales</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">৳{{ number_format($totalSales, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">🛒</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Outstanding Customer Due</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($totalDue, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📒</div>
        </div>
    </div>
</div>

<div class="card">
    @if($sales->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th style="text-align: right;">Amount</th>
                    <th style="text-align: right;">Paid</th>
                    <th style="text-align: right;">Due</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $s)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', ['sale' => $s->id]) }}" style="font-family: monospace; font-weight: 700; color: var(--primary);">
                            {{ $s->invoice_no }}
                        </a>
                    </td>
                    <td>{{ $s->sale_date->format('M d, Y') }}</td>
                    <td>{{ $s->customer?->name ?? 'Walk-in Cash Customer' }}</td>
                    <td><span class="badge badge-secondary">{{ $s->cashier?->name }}</span></td>
                    <td style="text-align: right; font-weight: 700;">৳{{ number_format($s->grand_total, 2) }}</td>
                    <td style="text-align: right; color: var(--success, #10b981);">৳{{ number_format($s->paid_amount, 2) }}</td>
                    <td style="text-align: right; color: var(--danger, #ef4444); font-weight: 700;">৳{{ number_format($s->due_amount, 2) }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $s->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($s->payment_status) }}</span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('sales.show', ['sale' => $s->id]) }}" target="_blank" class="action-btn" title="Print Thermal Slip">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </a>

                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
    <a href="{{ route('sales.edit', ['sale' => $s->id]) }}" class="btn btn-secondary">
        ✏️ Edit
    </a>

    <form action="{{ route('sales.destroy', ['sale' => $s->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ইনভয়েসটি বাতিল করবেন? স্টক ও বকেয়া আগের অবস্থায় ফিরে যাবে।');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
            🗑️
        </button>
    </form>
@endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($sales->hasPages()) <div class="pagination">{{ $sales->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No sales recorded yet</h3>
        <p class="empty-state-description">Open the POS Register to start checkout and ringing up sales.</p>
        <a href="{{ route('pos.index') }}" target="_blank" class="btn btn-primary">Open POS</a>
    </div>
    @endif
</div>
@endsection