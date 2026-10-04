@extends('tyro-dashboard::layouts.admin')

@section('title', 'Expense Categories')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Expense Categories</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Expense Categories (খরচের খাতসমূহ)</h1>
            <p class="page-description">Categorize daily shop expenses: Rent, Electricity, Staff food, Coolie, and Maintenance.</p>
        </div>
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">
            + Add Category
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
    @if($categories->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th style="text-align: center;">Total Expenses Recorded</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $c)
                <tr>
                    <td><strong style="color: var(--foreground);">{{ $c->name }}</strong></td>
                    <td><span class="badge badge-secondary" style="font-family: monospace;">{{ $c->code }}</span></td>
                    <td style="color: var(--muted-foreground);">{{ $c->description ?? 'N/A' }}</td>
                    <td style="text-align: center;"><span class="badge badge-primary">{{ $c->expenses_count }} Expenses</span></td>
                    <td style="text-align: center;">
                        <span class="badge {{ $c->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($c->status) }}</span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('expense-categories.edit', ['expense_category' => $c->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('expense-categories.destroy', ['expense_category' => $c->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?');">
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
    @if($categories->hasPages()) <div class="pagination">{{ $categories->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No expense categories found</h3>
        <p class="empty-state-description">Add categories (Rent, Utilities, Staff Food) to track daily expenses.</p>
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">Add Category</a>
    </div>
    @endif
</div>
@endsection