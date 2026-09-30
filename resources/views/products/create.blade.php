@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Product')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('products.index') }}">Products</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Product (নতুন পণ্য যোগ করুন)</h1>
            <p class="page-description">Fill in product details, pricing, barcodes, and stock levels.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            <!-- Product Names -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="name">Product Name (বাংলা) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="e.g. রূপচাঁদা সয়াবিন তেল ৫ লিটার" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="name_en">Product Name (English)</label>
                    <input type="text" id="name_en" name="name_en" class="form-input" placeholder="e.g. Rupchanda Soyabean Oil 5L" value="{{ old('name_en') }}">
                </div>
            </div>

            <!-- Barcode & SKU -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="barcode">Barcode (Gun Scan or Auto) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="barcode" name="barcode" class="form-input" value="{{ old('barcode', $autoBarcode) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="sku">SKU Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="sku" name="sku" class="form-input" value="{{ old('sku', $autoSku) }}" required style="font-family: monospace;">
                </div>
            </div>

            <!-- Classification -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="category_id">Category <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="brand_id">Brand / Company</label>
                    <select id="brand_id" name="brand_id" class="form-select">
                        <option value="">No Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="section_id">Store Section</label>
                    <select id="section_id" name="section_id" class="form-select">
                        <option value="">Not Assigned</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" {{ old('section_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="unit_id">Unit <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="unit_id" name="unit_id" class="form-select" required>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Pricing & Costing -->
            <div class="card" style="margin-bottom: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08));">
                <div class="card-body" style="padding: 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 0.75rem;">
                        Pricing & Profit Margins (মূল্য ও লাভ)
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="purchase_price">Purchase Cost (ক্রয়মূল্য) <span style="color: var(--danger, #ef4444);">*</span></label>
                            <input type="number" step="0.01" id="purchase_price" name="purchase_price" class="form-input" placeholder="85.00" value="{{ old('purchase_price') }}" required oninput="calcMargin()">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="selling_price">Retail MRP (খুচরা বিক্রয়মূল্য) <span style="color: var(--danger, #ef4444);">*</span></label>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-input" placeholder="100.00" value="{{ old('selling_price') }}" required oninput="calcMargin()">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="wholesale_price">Wholesale Price (পাইকারি)</label>
                            <input type="number" step="0.01" id="wholesale_price" name="wholesale_price" class="form-input" placeholder="92.00" value="{{ old('wholesale_price') }}">
                        </div>
                    </div>
                    <div id="marginPreview" style="margin-top: 0.5rem; font-size: 0.8125rem; font-weight: 600; color: var(--success, #10b981);">
                        Gross Profit: ৳0.00 (0.0% Margin)
                    </div>
                </div>
            </div>

            <!-- Stock & Alerts -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="initial_stock">Initial Stock (Active Branch)</label>
                    <input type="number" step="0.01" id="initial_stock" name="initial_stock" class="form-input" placeholder="50.00" value="{{ old('initial_stock', '10') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="alert_quantity">Low Stock Alert Level <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="alert_quantity" name="alert_quantity" class="form-input" value="{{ old('alert_quantity', '5.00') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input" value="{{ old('expiry_date') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" for="vat_percent">VAT / Tax Rate (%)</label>
                    <input type="number" step="0.1" id="vat_percent" name="vat_percent" class="form-input" value="{{ old('vat_percent', '0') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    function calcMargin() {
        const cost = parseFloat(document.getElementById('purchase_price').value) || 0;
        const sell = parseFloat(document.getElementById('selling_price').value) || 0;
        const preview = document.getElementById('marginPreview');

        if (sell > 0) {
            const profit = (sell - cost).toFixed(2);
            const margin = (((sell - cost) / sell) * 100).toFixed(1);
            preview.textContent = `Gross Profit: ৳${profit} (${margin}% Margin)`;
            preview.style.color = profit >= 0 ? 'var(--success, #10b981)' : 'var(--danger, #ef4444)';
        } else {
            preview.textContent = 'Gross Profit: ৳0.00 (0.0% Margin)';
        }
    }
</script>
@endsection