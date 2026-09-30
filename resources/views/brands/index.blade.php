@extends('tyro-dashboard::layouts.admin')

@section('title', 'Brands')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Brands</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Companies & Brands (কোম্পানি ও ব্র্যান্ড)</h1>
            <p class="page-description">Distributor companies, brand names, and sales representative contacts.</p>
        </div>
        <a href="{{ route('brands.create') }}" class="btn btn-primary">
            + Add Brand
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
    @if($brands->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Brand Name</th>
                    <th>Manufacturer / Company</th>
                    <th>SR / Contact Person</th>
                    <th>Phone Number</th>
                    <th style="text-align: center;">Total Products</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $b)
                <tr>
                    <td><strong style="color: var(--foreground);">{{ $b->name }}</strong></td>
                    <td>{{ $b->company_name ?? 'N/A' }}</td>
                    <td>{{ $b->contact_person ?? 'N/A' }}</td>
                    <td style="font-family: monospace;">{{ $b->phone ?? 'N/A' }}</td>
                    <td style="text-align: center;"><span class="badge badge-primary">{{ $b->products_count }} Products</span></td>
                    <td style="text-align: center;"><span class="badge {{ $b->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($b->status) }}</span></td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <a href="{{ route('brands.edit', ['brand' => $b->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('brands.destroy', ['brand' => $b->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this brand?');">
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
    @if($brands->hasPages()) <div class="pagination">{{ $brands->links() }}</div> @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No brands found</h3>
        <p class="empty-state-description">Add FMCG companies (Pran, Square, Unilever) to track products by distributor.</p>
        <a href="{{ route('brands.create') }}" class="btn btn-primary">Add Brand</a>
    </div>
    @endif
</div>
@endsection