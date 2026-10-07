@extends('tyro-dashboard::layouts.admin')

@section('title', 'Daily Product Profit Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Product Profit Report</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Daily Product Profit Report (পণ্যভিত্তিক লাভ রিপোর্ট)</h1>
            <p class="page-description">Detailed item-level profit margin analysis based on sale price and purchase cost snapshots.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Profit Report</button>
    </div>
</div>

<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('reports.daily-product-profit') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-input">
            </div>
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Calculate Profit</button>
        </form>
    </div>
</div>

<div class="card" id="printable-report" style="padding: 1.5rem;">
    @include('reports.partials.header', ['reportTitle' => 'Item-Level Profit & Margin Report (পণ্যভিত্তিক লাভ রিপোর্ট)'])

    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>SL</th>
                <th>Product Name (পণ্যের নাম)</th>
                <th>Category</th>
                <th style="text-align: right;">Qty Sold</th>
                <th style="text-align: right;">Avg Cost (৳)</th>
                <th style="text-align: right;">Avg Sale (৳)</th>
                <th style="text-align: right;">Total Cost (৳)</th>
                <th style="text-align: right;">Total Revenue (৳)</th>
                <th style="text-align: right;">Net Profit (৳)</th>
                <th style="text-align: right;">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $it)
            @php
                $itemProfit = $it->total_revenue - $it->total_cost;
                $margin = $it->total_revenue > 0 ? ($itemProfit / $it->total_revenue) * 100 : 0;
            @endphp
            <tr>
                <td style="font-family: monospace;">{{ $index + 1 }}</td>
                <td><strong>{{ $it->product?->name ?? 'Deleted Product' }}</strong></td>
                <td>{{ $it->product?->category?->name ?? '—' }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">
                    {{ number_format($it->total_qty, 2) }} {{ $it->product?->unit?->short_code }}
                </td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($it->avg_purchase_cost, 2) }}</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($it->avg_sale_price, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #475569;">৳{{ number_format($it->total_cost, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($it->total_revenue, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 800; color: {{ $itemProfit >= 0 ? '#15803d' : '#b91c1c' }};">
                    ৳{{ number_format($itemProfit, 2) }}
                </td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">
                    {{ number_format($margin, 1) }}%
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align: center;">কোনো বিক্রিত পণ্যের তথ্য পাওয়া যায়নি।</td></tr>
            @endforelse
        </tbody>
        <tfoot> 
            <tr style="background: var(--muted, rgba(148, 163, 184, 0.05)); font-weight: 800;">
                <td colspan="6" style="text-align: right;">সর্বমোট লাভ (Grand Profit):</td>
                <td style="text-align: right; font-family: monospace; color: #475569;">৳{{ number_format($totalCost, 2) }}</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalRevenue, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-size: 1rem; color: #15803d;">৳{{ number_format($totalProfit, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection