@extends('tyro-dashboard::layouts.admin')

@section('title', 'Products Catalog')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Products</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Products Catalog (পণ্য তালিকা)</h1>
            <p class="page-description">Manage retail inventory, barcodes, stock alerts, and wholesale/retail rates.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Product
        </a>
    </div>
</div>

<!-- Search & Filters Bar -->
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 1rem;">
        <form action="{{ route('products.index') }}" method="GET">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 220px;">
                    <input type="text" name="search" class="form-input" placeholder="Search by name, barcode, or SKU..." value="{{ request('search') }}">
                </div>
                <div>
                    <select name="category_id" class="form-select" style="min-width: 160px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="brand_id" class="form-select" style="min-width: 150px;">
                        <option value="">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->hasAny(['search', 'category_id', 'brand_id']))
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Clear</a>
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

<!-- Products Table -->
<div class="card">
    @if($products->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Product Details</th>
                    <th>Barcode / SKU</th>
                    <th>Category & Brand</th>
                    <th>Location / Section</th>
                    <th style="text-align: right;">Cost Price</th>
                    <th style="text-align: right;">Retail (MRP)</th>
                    <th style="text-align: center;">Branch Stock</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr>
                    <td>
                        <strong style="color: var(--foreground); font-size: 0.875rem;">{{ $p->name }}</strong>
                        @if($p->name_en)
                            <div style="font-size: 0.75rem; color: var(--muted-foreground);">{{ $p->name_en }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary" style="font-family: monospace;">{{ $p->barcode }}</span>
                    </td>
                    <td>
                        <div>{{ $p->category?->name ?? 'N/A' }}</div>
                        <div style="font-size: 0.75rem; color: var(--muted-foreground);">{{ $p->brand?->name ?? 'No Brand' }}</div>
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem;">{{ $p->section?->name ?? 'Not Assigned' }}</span>
                    </td>
                    <td style="text-align: right; font-weight: 500;">৳{{ number_format($p->purchase_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 700; color: var(--primary);">
                        ৳{{ number_format($p->selling_price, 2) }}
                        <div style="font-size: 0.7rem; color: var(--success, #10b981); font-weight: 600;">+{{ $p->profitMargin() }}%</div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $currStock = $p->currentStock();
                            $isLow = $p->isLowStock();
                        @endphp
                        <span class="badge {{ $isLow ? 'badge-danger' : 'badge-success' }}" style="font-size: 0.75rem;">
                            {{ $currStock }} {{ $p->unit?->short_code }}
                        </span>
                        @if($isLow)
                            <div style="font-size: 0.65rem; color: var(--danger, #ef4444); font-weight: 700;">LOW STOCK</div>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <!-- Print Barcode Sticker -->
                            <a href="{{ route('products.barcodes', ['product' => $p->id]) }}" target="_blank" class="action-btn" title="Print Barcode Stickers">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </a>
                            <!-- Edit -->
                            <a href="{{ route('products.edit', ['product' => $p->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <!-- Delete -->
                            <form action="{{ route('products.destroy', ['product' => $p->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
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

    @if($products->hasPages())
    <div class="pagination">
        {{ $products->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <h3 class="empty-state-title">No products found</h3>
        <p class="empty-state-description">Add products with barcodes, purchase rates, and retail prices to start selling.</p>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
    </div>
    @endif
</div>
@endsection