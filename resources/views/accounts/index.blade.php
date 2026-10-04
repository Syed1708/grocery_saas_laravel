@extends('tyro-dashboard::layouts.admin')

@section('title', 'Accounts & Wallets')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Banking & Accounts</span>
<span class="breadcrumb-separator">/</span>
<span>Accounts List</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Accounts & Wallets (হিসাব ও ওয়ালেট তালিকা)</h1>
            <p class="page-description">Manage cash drawers, bank accounts, and mobile financial services (bKash/Nagad).</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('transfers.create') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Fund Transfer (টাকা স্থানান্তর)
            </a>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Account
            </a>
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

@if(session('error'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--danger, #ef4444); background: rgba(239, 68, 68, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--danger, #ef4444); font-weight: 500; font-size: 0.875rem;">
        {{ session('error') }}
    </div>
</div>
@endif

<!-- Summary Cards -->
<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">মোট ব্যালেন্স (All Accounts)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary, #0284c7); font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($totalBalance ?? 0, 2) }}
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">ক্যাশ ড্রয়ার (Cash in Hand)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--success, #10b981); font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($cashBalance ?? 0, 2) }}
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">ব্যাংক ব্যালেন্স (Bank Balance)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: #6366f1; font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($bankBalance ?? 0, 2) }}
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">MFS (বিকাশ / নগদ / রকেট)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: #f59e0b; font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($mfsBalance ?? 0, 2) }}
            </span>
        </div>
    </div>
</div>

<div class="card">
    @if($accounts->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Type</th>
                    <th>Account / Phone Number</th>
                    <th>Bank & Branch</th>
                    <th style="text-align: right;">Current Balance</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $acc)
                <tr>
                    <td>
                        <strong style="color: var(--foreground); font-size: 0.875rem;">{{ $acc->name }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $acc->type === 'cash' ? 'badge-success' : ($acc->type === 'bank' ? 'badge-primary' : 'badge-secondary') }}" style="text-transform: uppercase; font-size: 10px;">
                            {{ $acc->type }}
                        </span>
                    </td>
                    <td style="font-family: monospace;">
                        {{ $acc->account_number ?? '—' }}
                    </td>
                    <td style="color: var(--muted-foreground);">
                        {{ $acc->bank_name ? $acc->bank_name . ($acc->branch_name ? ' (' . $acc->branch_name . ')' : '') : '—' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; font-size: 0.95rem; color: var(--foreground);">
                        ৳{{ number_format($acc->current_balance, 2) }}
                    </td>
                    <td style="text-align: center;">
                        @if($acc->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('accounts.show', ['account' => $acc->id]) }}" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="View Statement">
                                খতিয়ান
                            </a>
                            <a href="{{ route('accounts.edit', ['account' => $acc->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('accounts.destroy', ['account' => $acc->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this account?');">
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

    @if($accounts->hasPages())
    <div class="pagination">
        {{ $accounts->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No accounts found</h3>
        <p class="empty-state-description">Add cash drawers, bank accounts, or bKash/Nagad merchant wallets to start tracking funds.</p>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary">Add Account</a>
    </div>
    @endif
</div>
@endsection