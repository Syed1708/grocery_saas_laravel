@extends('tyro-dashboard::layouts.admin')

@section('title', 'Suppliers List')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Suppliers</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Suppliers & Distributors (মহাজন ও ডিলার তালিকা)</h1>
            <p class="page-description">Manage wholesale suppliers, distributor agencies, and supplier dues ledger.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Supplier
        </a>
    </div>
</div>

<!-- Total Payable Due Metric -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Supplier Payable Due (মোট মহাজন বাকি)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($totalDue, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📋</div>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 1rem;">
        <form action="{{ route('suppliers.index') }}" method="GET">
            <div style="display: flex; gap: 0.75rem;">
                <input type="text" name="search" class="form-input" placeholder="Search by name, company, or phone..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Search</button>
                @if(request('search'))
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Clear</a>
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
    @if($suppliers->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Supplier / Mahajon</th>
                    <th>Distributor Agency</th>
                    <th>SR / Contact Person</th>
                    <th>Phone Number</th>
                    <th style="text-align: right;">Current Due (বাকি)</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $s)
                <tr>
                    <td>
                        <a href="{{ route('suppliers.show', ['supplier' => $s->id]) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $s->name }}
                        </a>
                    </td>
                    <td>{{ $s->company_name ?? 'N/A' }}</td>
                    <td>{{ $s->contact_person ?? 'N/A' }}</td>
                    <td style="font-family: monospace;">{{ $s->phone }}</td>
                    <td style="text-align: right; font-weight: 700; color: {{ $s->current_due > 0 ? 'var(--danger, #ef4444)' : 'var(--success, #10b981)' }};">
                        ৳{{ number_format($s->current_due, 2) }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $s->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            @if($s->current_due > 0)
                                <a href="{{ route('suppliers.payment.create', ['supplier' => $s->id]) }}" class="btn btn-primary" style="padding: 2px 8px; font-size: 11px;">
                                    Pay Due
                                </a>
                            @endif
                            <a href="{{ route('suppliers.show', ['supplier' => $s->id]) }}" class="action-btn" title="View Ledger">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('suppliers.edit', ['supplier' => $s->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages()) <div class="pagination">{{ $suppliers->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No suppliers found</h3>
        <p class="empty-state-description">Add distributors and mahajons to manage stock procurement.</p>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
    </div>
    @endif
</div>
@endsection