@extends('tyro-dashboard::layouts.admin')

@section('title', 'Daily Expenses')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Expenses</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Daily Shop Expenses (দৈনিক দোকান খরচ)</h1>
            <p class="page-description">Record petty cash expenses, rent, utilities, coolie, and staff refreshments.</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            + Record Expense
        </a>
    </div>
</div>

<!-- Summary Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Expenses This Month (চলতি মাসের খরচ)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($totalThisMonth, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📉</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Overall Expenses (সর্বমোট খরচ)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--foreground); margin-top: 0.25rem;">৳{{ number_format($totalOverall, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(148, 163, 184, 0.1); color: var(--muted-foreground); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📋</div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 1rem;">
        <form action="{{ route('expenses.index') }}" method="GET">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label" style="font-size: 11px;">Expense Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px;">From Date</label>
                    <input type="date" name="from_date" class="form-input" value="{{ request('from_date') }}">
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px;">To Date</label>
                    <input type="date" name="to_date" class="form-input" value="{{ request('to_date') }}">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->hasAny(['category_id', 'from_date', 'to_date']))
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>
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
    @if($expenses->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Category (খাত)</th>
                    <th>Spender / Cashier</th>
                    <th>Payment Method</th>
                    <th>Note / Reference</th>
                    <th style="text-align: right;">Amount (৳)</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $e)
                <tr>
                    <td><strong style="font-family: monospace;">{{ $e->voucher_no }}</strong></td>
                    <td>{{ $e->expense_date->format('M d, Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $e->category?->name }}</span></td>
                    <td>{{ $e->user?->name ?? 'Staff' }}</td>
                    <td><span class="badge badge-primary">{{ strtoupper($e->payment_method) }}</span></td>
                    <td style="color: var(--muted-foreground); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $e->note ?? $e->reference ?? 'N/A' }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: var(--danger, #ef4444);">
                        ৳{{ number_format($e->amount, 2) }}
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('expenses.edit', ['expense' => $e->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('expenses.destroy', ['expense' => $e->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this expense?');">
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
    @if($expenses->hasPages()) <div class="pagination">{{ $expenses->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No expenses recorded</h3>
        <p class="empty-state-description">Record daily store expenses to calculate accurate net profit.</p>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">Record Expense</a>
    </div>
    @endif
</div>
@endsection