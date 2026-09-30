@extends('tyro-dashboard::layouts.admin')

@section('title', 'Measurement Units')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Units</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Measurement Units (পরিমাপের একক)</h1>
            <p class="page-description">Configure primary units and wholesale-to-retail conversion ratios (e.g. 1 Sack = 50 Kg).</p>
        </div>
        <a href="{{ route('units.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Unit
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

<div class="card">
    @if($units->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Unit Name</th>
                    <th>Code</th>
                    <th style="text-align: center;">Decimals Allowed</th>
                    <th>Conversion Formula</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $u)
                <tr>
                    <td>
                        <strong style="color: var(--foreground);">{{ $u->name }}</strong>
                    </td>
                    <td><span class="badge badge-secondary" style="font-family: monospace;">{{ $u->short_code }}</span></td>
                    <td style="text-align: center;">
                        @if($u->allow_decimal)
                            <span class="badge badge-primary">Yes (1.25)</span>
                        @else
                            <span class="badge badge-secondary">Integer (1, 2)</span>
                        @endif
                    </td>
                    <td>
                        @if($u->baseUnit)
                            <span style="font-size: 0.8125rem; font-weight: 600;">
                                1 {{ $u->short_code }} = {{ rtrim(rtrim($u->conversion_rate, '0'), '.') }} {{ $u->baseUnit->short_code }}
                            </span>
                        @else
                            <span style="color: var(--muted-foreground); font-size: 0.75rem;">Base Unit (প্রধান একক)</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($u->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('units.edit', ['unit' => $u->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('units.destroy', ['unit' => $u->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this unit?');">
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

    @if($units->hasPages())
    <div class="pagination">
        {{ $units->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No measurement units found</h3>
        <p class="empty-state-description">Get started by creating your first unit (Kg, Pcs, Liter).</p>
        <a href="{{ route('units.create') }}" class="btn btn-primary">Add Unit</a>
    </div>
    @endif
</div>
@endsection