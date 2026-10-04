@extends('tyro-dashboard::layouts.admin')

@section('title', 'Staff List')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Staff</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Staff Directory (কর্মচারী তালিকা)</h1>
            <p class="page-description">Manage store employees, monthly salaries, advances, and designations.</p>
        </div>
        <a href="{{ route('staff.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Staff
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Active Monthly Salaries (মূল বেতন বাজেট)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">৳{{ number_format($totalMonthlyPayroll, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">👥</div>
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
    @if($staff->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Branch</th>
                    <th>Phone</th>
                    <th style="text-align: right;">Base Salary</th>
                    <th style="text-align: right;">Pending Advance</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $st)
                <tr>
                    <td>
                        <a href="{{ route('staff.show', ['staff' => $st->id]) }}" style="font-weight: 700; color: var(--foreground); text-decoration: none;">
                            {{ $st->name }}
                        </a>
                        @if($st->nid_number)
                            <div style="font-size: 10px; color: var(--muted-foreground); font-family: monospace;">NID: {{ $st->nid_number }}</div>
                        @endif
                    </td>
                    <td><strong style="color: var(--primary);">{{ $st->designation }}</strong></td>
                    <td><span class="badge badge-secondary">{{ $st->department?->name }}</span></td>
                    <td>{{ $st->branch?->name }}</td>
                    <td style="font-family: monospace;">{{ $st->phone }}</td>
                    <td style="text-align: right; font-weight: 600;">৳{{ number_format($st->basic_salary, 2) }}</td>
                    <td style="text-align: right; font-weight: 700; color: {{ $st->pendingAdvance() > 0 ? 'var(--danger, #ef4444)' : 'var(--muted-foreground)' }};">
                        ৳{{ number_format($st->pendingAdvance(), 2) }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $st->status === 'active' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($st->status) }}</span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('staff.show', ['staff' => $st->id]) }}" class="action-btn" title="View Profile & Salary Sheet">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('staff.edit', ['staff' => $st->id]) }}" class="action-btn" title="Edit">
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
    @if($staff->hasPages()) <div class="pagination">{{ $staff->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No staff profiles registered</h3>
        <p class="empty-state-description">Add store employees, cashiers, salesmen, and set up their monthly base salaries.</p>
        <a href="{{ route('staff.create') }}" class="btn btn-primary">Add Staff</a>
    </div>
    @endif
</div>
@endsection