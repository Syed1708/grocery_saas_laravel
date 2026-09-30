@extends('tyro-dashboard::layouts.admin')

@section('title', 'Owner Capital & Drawings')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Owner Equity</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Owner Capital & Drawings (মূলধন ও ব্যক্তিগত উত্তোলন)</h1>
            <p class="page-description">Track capital injections and personal withdrawals to keep family finances separate from store cash.</p>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Capital Invested (+)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success, #10b981); margin-top: 0.25rem;">৳{{ number_format($totalCapital, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--success, #10b981); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📥</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Personal Withdrawals (-)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($totalDrawing, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📤</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Net Owner's Equity</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary, #0ea5e9); margin-top: 0.25rem;">৳{{ number_format($netEquity, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: var(--primary, #0ea5e9); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">⚖️</div>
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

<!-- Quick Transaction Form -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div style="font-size: 0.875rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--foreground);">+ Record Capital / Drawing</div>
        <form action="{{ route('equity.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) 120px; gap: 0.75rem; align-items: flex-end;">
                <div class="form-group">
                    <label class="form-label" for="type">Type</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="capital">➕ Capital Injected (+)</option>
                        <option value="drawing">➖ Personal Drawing (-)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="transaction_date">Date</label>
                    <input type="date" id="transaction_date" name="transaction_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Amount (৳)</label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="50000.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_method">Method</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Account</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="purpose">Purpose / Note</label>
                    <input type="text" id="purpose" name="purpose" class="form-input" placeholder="e.g. Personal family expense">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    @if($transactions->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th>Purpose / Note</th>
                    <th style="text-align: right;">Amount (৳)</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr>
                    <td style="font-family: monospace;">{{ $t->transaction_date->format('M d, Y') }}</td>
                    <td>
                        @if($t->type === 'capital')
                            <span class="badge badge-success">Capital Invested</span>
                        @else
                            <span class="badge badge-danger">Personal Drawing</span>
                        @endif
                    </td>
                    <td><span class="badge badge-secondary">{{ strtoupper($t->payment_method) }}</span></td>
                    <td>{{ $t->purpose ?? 'N/A' }}</td>
                    <td style="text-align: right; font-weight: 700; color: {{ $t->type === 'capital' ? 'var(--success, #10b981)' : 'var(--danger, #ef4444)' }};">
                        {{ $t->type === 'capital' ? '+' : '-' }}৳{{ number_format($t->amount, 2) }}
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <form action="{{ route('equity.destroy', ['transaction' => $t->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this transaction?');">
                                @csrf
                                @method('DELETE')
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

    @if($transactions->hasPages())
    <div class="pagination">
        {{ $transactions->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No transactions recorded</h3>
        <p class="empty-state-description">Record your initial business capital to establish your store's net equity.</p>
    </div>
    @endif
</div>
@endsection