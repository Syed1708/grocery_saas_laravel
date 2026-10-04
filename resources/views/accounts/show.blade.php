@extends('tyro-dashboard::layouts.admin')

@section('title', 'Account Statement')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('accounts.index') }}">Accounts</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $account->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $account->name }} (হিসাব বিবরণী)</h1>
            <p class="page-description">Complete ledger statement, deposits, withdrawals, and balance history.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('transfers.create') }}" class="btn btn-secondary">🔄 Transfer</a>
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">← Back to List</a>
        </div>
    </div>
</div>

<!-- Account Info Banner -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: center;">
        <div>
            <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">Account Type</span>
            <strong style="display: block; font-size: 1rem; text-transform: uppercase;">{{ $account->type }}</strong>
        </div>
        <div>
            <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">A/C Number</span>
            <strong style="display: block; font-size: 1rem; font-family: monospace;">{{ $account->account_number ?? 'N/A' }}</strong>
        </div>
        <div>
            <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">Bank & Branch</span>
            <strong style="display: block; font-size: 1rem;">{{ $account->bank_name ? $account->bank_name . ' (' . ($account->branch_name ?? 'Main') . ')' : '—' }}</strong>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">Current Live Balance</span>
            <strong style="display: block; font-size: 1.5rem; color: var(--success, #10b981); font-family: monospace;">৳{{ number_format($account->current_balance, 2) }}</strong>
        </div>
    </div>
</div>

<!-- Statement Filter Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('accounts.show', $account) }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-input">
            </div>
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter</button>
            <a href="{{ route('accounts.show', $account) }}" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Reset</a>
        </form>
    </div>
</div>

<!-- Ledger Transactions Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Trx Code</th>
                    <th>Type / Description</th>
                    <th style="text-align: right;">Debit (খরচ / প্রদান)</th>
                    <th style="text-align: right;">Credit (জমা / গ্রহণ)</th>
                    <th style="text-align: right;">Balance After</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                @php
                    $isDebit = in_array($trx->type, ['withdraw', 'transfer_out', 'purchase', 'expense', 'payroll']);
                @endphp
                <tr>
                    <td style="font-family: monospace; color: var(--muted-foreground);">
                        {{ $trx->transaction_date->format('d M, Y') }}
                    </td>
                    <td style="font-family: monospace; font-size: 0.75rem;">
                        {{ $trx->trx_code }}
                    </td>
                    <td>
                        <span class="badge {{ $isDebit ? 'badge-danger' : 'badge-success' }}" style="text-transform: uppercase; font-size: 10px; margin-right: 4px;">
                            {{ str_replace('_', ' ', $trx->type) }}
                        </span>
                        <span style="font-size: 0.8125rem; color: var(--foreground);">{{ $trx->note ?? '—' }}</span>
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--danger, #ef4444);">
                        {{ $isDebit ? '৳' . number_format($trx->amount, 2) : '—' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">
                        {{ !$isDebit ? '৳' . number_format($trx->amount, 2) : '—' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 800; color: var(--foreground);">
                        ৳{{ number_format($trx->balance_after, 2) }}
                    </td>
                    <td style="font-size: 0.75rem; color: var(--muted-foreground);">
                        {{ $trx->user?->name ?? 'System' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--muted-foreground);">
                        এই অ্যাকাউন্টে কোনো লেনদেনের রেকর্ড নেই।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="pagination">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection