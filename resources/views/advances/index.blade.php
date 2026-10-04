@extends('tyro-dashboard::layouts.admin')

@section('title', 'Advance Salaries')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Advance Salary</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Advance Salaries & Loans (অগ্রিম বেতন / হাওলাত খাতা)</h1>
            <p class="page-description">Record staff mid-month cash advances. Automatically deducted during monthly payroll generation.</p>
        </div>
        <a href="{{ route('advances.create') }}" class="btn btn-primary">
            + Give Advance Salary
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--muted-foreground); font-weight: 600;">Total Pending Advances (মোট অগ্রিম বকেয়া)</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger, #ef4444); margin-top: 0.25rem;">৳{{ number_format($totalPendingAdvance, 2) }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">💸</div>
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
    @if($advances->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Designation</th>
                    <th>Date</th>
                    <th>Purpose / Reason</th>
                    <th>Paid Via</th>
                    <th style="text-align: right;">Amount (৳)</th>
                    <th style="text-align: center;">Deduction Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($advances as $adv)
                <tr>
                    <td><strong>{{ $adv->staff?->name }}</strong></td>
                    <td>{{ $adv->staff?->designation }}</td>
                    <td>{{ $adv->advance_date->format('M d, Y') }}</td>
                    <td style="color: var(--muted-foreground);">{{ $adv->purpose ?? 'N/A' }}</td>
                    <td><span class="badge badge-secondary">{{ strtoupper($adv->payment_method) }}</span></td>
                    <td style="text-align: right; font-weight: 800; color: var(--danger, #ef4444);">
                        ৳{{ number_format($adv->amount, 2) }}
                    </td>
                    <td style="text-align: center;">
                        @if($adv->is_deducted)
                            <span class="badge badge-success">Deducted from Salary</span>
                        @else
                            <span class="badge badge-danger">Pending Deduction</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        @if(!$adv->is_deducted)
                            <form action="{{ route('advances.destroy', ['advance' => $adv->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this advance record?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-danger" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($advances->hasPages()) <div class="pagination">{{ $advances->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No advance salaries recorded</h3>
        <p class="empty-state-description">Record emergency cash advances to automatically deduct them at the end of the month.</p>
        <a href="{{ route('advances.create') }}" class="btn btn-primary">Give Advance</a>
    </div>
    @endif
</div>
@endsection