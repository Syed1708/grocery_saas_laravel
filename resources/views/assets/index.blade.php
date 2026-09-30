@extends('tyro-dashboard::layouts.admin')

@section('title', 'Company Assets')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Assets</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Company Assets (দোকানের স্থায়ী সম্পদ)</h1>
            <p class="page-description">Track store equipment, deep freezers, IPS, weighing scales, and current valuations.</p>
        </div>
        <a href="{{ route('assets.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Asset
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Purchase Cost</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--foreground); margin-top: 0.25rem;">৳{{ number_format($totalCost, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">💰</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Current Valuation</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success, #10b981); margin-top: 0.25rem;">৳{{ number_format($currentValuation, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--success, #10b981); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">📉</div>
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
    @if($assets->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Asset Name</th>
                    <th>Tag Code</th>
                    <th>Branch</th>
                    <th>Purchase Date</th>
                    <th style="text-align: right;">Cost Price</th>
                    <th style="text-align: right;">Current Value</th>
                    <th style="text-align: center;">Condition</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $a)
                <tr>
                    <td>
                        <strong style="color: var(--foreground);">{{ $a->name }}</strong>
                        @if($a->serial_number)
                            <div style="font-size: 0.75rem; color: var(--muted-foreground); font-family: monospace;">S/N: {{ $a->serial_number }}</div>
                        @endif
                    </td>
                    <td><span class="badge badge-secondary" style="font-family: monospace;">{{ $a->asset_code }}</span></td>
                    <td>{{ $a->branch?->name ?? 'All Branches' }}</td>
                    <td>{{ $a->purchase_date->format('M d, Y') }}</td>
                    <td style="text-align: right; font-weight: 500;">৳{{ number_format($a->purchase_cost, 2) }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--success, #10b981);">৳{{ number_format($a->current_value, 2) }}</td>
                    <td style="text-align: center;">
                        @php
                            $badgeMap = [
                                'good'        => ['Good', 'badge-success'],
                                'maintenance' => ['Servicing', 'badge-warning'],
                                'damaged'     => ['Damaged', 'badge-danger'],
                                'disposed'    => ['Disposed', 'badge-secondary'],
                            ];
                        @endphp
                        <span class="badge {{ $badgeMap[$a->condition][1] }}">
                            {{ $badgeMap[$a->condition][0] }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('assets.edit', ['asset' => $a->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('assets.destroy', ['asset' => $a->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this asset?');">
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

    @if($assets->hasPages())
    <div class="pagination">
        {{ $assets->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No company assets found</h3>
        <p class="empty-state-description">Add refrigerators, weighing scales, IPS, or racks to track your store valuation.</p>
        <a href="{{ route('assets.create') }}" class="btn btn-primary">Add Asset</a>
    </div>
    @endif
</div>
@endsection