@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Product')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('products.index') }}">Products</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Product (পণ্য এডিট)</h1>
            <p class="page-description">Update product pricing, barcodes, and category assignments.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('products.update', ['product' => $product->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Product Name (বাংলা) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="name_en">Product Name (English)</label>
                    <input type="text" id="name_en" name="name_en" class="form-input" value="{{ old('name_en', $product->name_en) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="barcode">Barcode <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="barcode" name="barcode" class="form-input" value="{{ old('barcode', $product->barcode) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="sku">SKU Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="sku" name="sku" class="form-input" value="{{ old('sku', $product->sku) }}" required style="font-family: monospace;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ $product->category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="brand_id">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select">
                        <option value="">No Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ $product->brand_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="section_id">Section</label>
                    <select id="section_id" name="section_id" class="form-select">
                        <option value="">Not Assigned</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" {{ $product->section_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id" class="form-select" required>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $product->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card" style="margin-bottom: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08));">
                <div class="card-body" style="padding: 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 0.75rem;">
                        Pricing & Profit Margins
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="purchase_price">Cost Price (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                            <input type="number" step="0.01" id="purchase_price" name="purchase_price" class="form-input" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="selling_price">Retail MRP (৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-input" value="{{ old('selling_price', $product->selling_price) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="wholesale_price">Wholesale Price (৳)</label>
                            <input type="number" step="0.01" id="wholesale_price" name="wholesale_price" class="form-input" value="{{ old('wholesale_price', $product->wholesale_price) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="alert_quantity">Low Stock Alert Quantity</label>
                    <input type="number" step="0.01" id="alert_quantity" name="alert_quantity" class="form-input" value="{{ old('alert_quantity', $product->alert_quantity) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input" value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $product->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>
@endsection