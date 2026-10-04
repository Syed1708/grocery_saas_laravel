@extends('tyro-dashboard::layouts.admin')

@section('title', 'Bank Cheques')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Banking & Accounts</span>
<span class="breadcrumb-separator">/</span>
<span>Bank Cheques</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Bank Cheques Registry (চেক রেজিস্ট্রি)</h1>
            <p class="page-description">Track cheques received from customers and cheques issued to suppliers.</p>
        </div>
        <a href="{{ route('cheques.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Cheque Entry
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
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">অপেক্ষমান চেক (Pending Cheques)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: #f59e0b; font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($totalPending, 2) }}
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding: 1.25rem;">
            <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); display: block;">ক্লিয়ার্ড চেক (Cleared Cheques)</span>
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--success, #10b981); font-family: monospace; display: block; margin-top: 0.25rem;">
                ৳{{ number_format($totalCleared, 2) }}
            </span>
        </div>
    </div>
</div>

<div class="card">
    @if($cheques->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Party / Person</th>
                    <th>Bank & Cheque No</th>
                    <th>Cheque Date</th>
                    <th style="text-align: right;">Amount</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Action / Clear</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cheques as $chq)
                <tr>
                    <td>
                        <span class="badge {{ $chq->type === 'received' ? 'badge-success' : 'badge-danger' }}" style="font-size: 10px;">
                            {{ $chq->type === 'received' ? '📥 RECEIVED' : '📤 ISSUED' }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: var(--foreground); font-size: 0.875rem;">{{ $chq->party_name }}</strong>
                    </td>
                    <td>
                        <span>{{ $chq->bank_name }}</span>
                        <div style="font-family: monospace; font-size: 0.75rem; color: var(--muted-foreground);">#{{ $chq->cheque_number }}</div>
                    </td>
                    <td style="font-family: monospace; color: var(--muted-foreground);">
                        {{ $chq->cheque_date->format('d M, Y') }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--foreground);">
                        ৳{{ number_format($chq->amount, 2) }}
                    </td>
                    <td style="text-align: center;">
                        @if($chq->status === 'cleared')
                            <span class="badge badge-success">Cleared</span>
                        @elseif($chq->status === 'bounced')
                            <span class="badge badge-danger">Bounced</span>
                        @else
                            <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Pending</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if($chq->status === 'pending')
                            <form action="{{ route('cheques.status', ['cheque' => $chq->id]) }}" method="POST" style="display: inline-flex; gap: 0.25rem; align-items: center; justify-content: flex-end;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cleared">
                                <select name="deposit_account_id" required class="form-select" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: auto;">
                                    <option value="">Select Bank...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Mark Cleared">
                                    Clear
                                </button>
                            </form>
                        @else
                            <span style="font-size: 0.75rem; color: var(--muted-foreground);">{{ $chq->clearing_date ? $chq->clearing_date->format('d M, Y') : '—' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($cheques->hasPages())
    <div class="pagination">
        {{ $cheques->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No cheques recorded</h3>
        <p class="empty-state-description">Record incoming customer cheques and outgoing supplier cheques.</p>
        <a href="{{ route('cheques.create') }}" class="btn btn-primary">Add Cheque</a>
    </div>
    @endif
</div>
@endsection