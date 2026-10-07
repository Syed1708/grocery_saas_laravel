@extends('tyro-dashboard::layouts.admin')

@section('title', 'Product Stock Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Stock Report</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Product Stock & Inventory Report (পণ্য মজুদ খতিয়ান)</h1>
            <p class="page-description">Filter inventory valuation at wholesale cost vs retail selling price.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Stock Report</button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('reports.stock') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; min-width: 200px;">
                <label class="form-label" style="font-size: 0.75rem;">পণ্য খুঁজুন (Name / Barcode / SKU)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. রূপচাঁদা / 89411..." class="form-input">
            </div>
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">ক্যাটাগরি (Category)</label>
                <select name="category_id" class="form-select">
                    <option value="">সকল ক্যাটাগরি</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">স্টক স্ট্যাটাস (Stock Status)</label>
                <select name="stock_status" class="form-select">
                    <option value="all" {{ request('stock_status') == 'all' ? 'selected' : '' }}>সকল পণ্য (All)</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>পর্যাপ্ত স্টক (In Stock)</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>কম স্টক (Low Stock)</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>স্টক শেষ (Out of Stock)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter Stock</button>
            <a href="{{ route('reports.stock') }}" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Reset</a>
        </form>
    </div>
</div>

<div class="card" id="printable-report" style="padding: 1.5rem;">
    @include('reports.partials.header', ['reportTitle' => 'Product Stock Valuation Report (পণ্য মজুদ ও মূল্যায়ন খতিয়ান)'])

    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>SL</th>
                <th>Barcode / SKU</th>
                <th>Product Name (পণ্যের নাম)</th>
                <th>Category</th>
                <th style="text-align: right;">Current Stock</th>
                <th style="text-align: right;">Unit Cost (৳)</th>
                <th style="text-align: right;">Total Cost (৳)</th>
                <th style="text-align: right;">Unit MRP (৳)</th>
                <th style="text-align: right;">Total Retail (৳)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $p)
            <tr>
                <td style="font-family: monospace;">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $p->barcode }}</td>
                <td><strong>{{ $p->name }}</strong></td>
                <td>{{ $p->category?->name ?? '—' }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">
                    {{ number_format($p->current_stock_count, 2) }} {{ $p->unit?->short_code }}
                </td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($p->purchase_price, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #475569;">
                    ৳{{ number_format($p->stock_cost_value, 2) }}
                </td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($p->selling_price, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0284c7;">
                    ৳{{ number_format($p->stock_retail_value, 2) }}
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align: center;">কোনো পণ্য পাওয়া যায়নি।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: var(--muted, rgba(148, 163, 184, 0.05)); font-weight: 800;">
                <td colspan="6" style="text-align: right;">সর্বমোট মূল্যায়ন (Total Valuation):</td>
                <td style="text-align: right; font-family: monospace; color: #475569;">৳{{ number_format($totalCostValuation, 2) }}</td>
                <td></td>
                <td style="text-align: right; font-family: monospace; color: #0284c7;">৳{{ number_format($totalRetailValuation, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection