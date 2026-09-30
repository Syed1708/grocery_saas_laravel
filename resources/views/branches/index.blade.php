@extends('tyro-dashboard::layouts.admin')

@section('title', 'Branches')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Branches</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Store Branches (শাখা ও আউটলেট)</h1>
            <p class="page-description">Manage main store and branch outlets, counters, and branch-specific staff.</p>
        </div>
        <a href="{{ route('branches.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Branch
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

<div class="card">
    @if($branches->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Branch Code</th>
                    <th>Phone Number</th>
                    <th>Location / Address</th>
                    <th style="text-align: center;">Assigned Staff</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $b)
                <tr>
                    <td>
                        <strong style="color: var(--foreground); font-size: 0.875rem;">{{ $b->name }}</strong>
                        @if($b->is_main)
                            <span class="badge badge-primary" style="margin-left: 6px; font-size: 10px;">MAIN BRANCH</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary" style="font-family: monospace;">{{ $b->code }}</span>
                    </td>
                    <td style="font-family: monospace;">{{ $b->phone ?? 'N/A' }}</td>
                    <td style="color: var(--muted-foreground); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $b->address ?? 'N/A' }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-secondary">
                            {{ $b->users_count }} Staff
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if($b->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('branches.edit', ['branch' => $b->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            @if(!$b->is_main)
                                <form action="{{ route('branches.destroy', ['branch' => $b->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this branch?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-danger" title="Delete">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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

    @if($branches->hasPages())
    <div class="pagination">
        {{ $branches->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No branches found</h3>
        <p class="empty-state-description">Add store branches and outlets to manage branch-specific inventory.</p>
        <a href="{{ route('branches.create') }}" class="btn btn-primary">Add Branch</a>
    </div>
    @endif
</div>
@endsection