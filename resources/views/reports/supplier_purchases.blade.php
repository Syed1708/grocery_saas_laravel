@extends('tyro-dashboard::layouts.admin')

@section('title', 'Supplier Purchase Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Supplier Purchases</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Supplier Purchase Report (মহাজনভিত্তিক ক্রয় ও খাতা)</h1>
            <p class="page-description">Search or select supplier to inspect purchase invoices, payments, and balances.</p>
        </div>
        @if($selectedSupplier)
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
        @endif
    </div>
</div>

<!-- Dual Search & Filter Card -->
<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1.25rem;">
        <form method="GET" action="{{ route('reports.supplier-purchases') }}" id="supplierFilterForm">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: flex-end;">
                
                <!-- Option 1: Live Type Search -->
                <div class="form-group" style="margin: 0; position: relative;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">🔍 Option 1: Search Supplier (নাম বা ফোন)</label>
                    <input type="text" id="supplier_search_input" placeholder="টাইপ করে মহাজন খুঁজুন..." class="form-input" autocomplete="off" oninput="searchSuppliersAjax(this.value)">
                    <div id="supplier_search_results" style="display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 50; background: #0f172a; border: 1px solid #334155; border-radius: 8px; max-height: 200px; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5);"></div>
                </div>

                <!-- Option 2: Select Dropdown -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">📋 Option 2: Select from List <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select name="supplier_id" id="supplier_dropdown" class="form-select" required onchange="document.getElementById('supplierFilterForm').submit()">
                        <option value="">মহাজন নির্বাচন করুন...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->company_name ?? 'N/A' }}) - বকেয়া: ৳{{ number_format($s->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-input">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-input">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.55rem 1rem;">Show Ledger</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedSupplier)
<div class="card" id="printable-report" style="padding: 1.5rem;">
    @include('reports.partials.header', [
        'reportTitle' => 'Supplier Ledger: ' . $selectedSupplier->name . ' (' . ($selectedSupplier->company_name ?? 'N/A') . ')'
    ])

    <!-- Supplier Overview Banner -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
        <div>
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Supplier Name</span>
            <strong style="display: block; font-size: 0.95rem; color: #000;">{{ $selectedSupplier->name }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Company / Agency</span>
            <strong style="display: block; font-size: 0.95rem; color: #000;">{{ $selectedSupplier->company_name ?? 'N/A' }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Phone Number</span>
            <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: #000;">{{ $selectedSupplier->phone }}</strong>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Current Due (মোট বকেয়া দেনা)</span>
            <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: #b91c1c;">৳{{ number_format($selectedSupplier->current_due, 2) }}</strong>
        </div>
    </div>

    <!-- Purchases Table -->
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Chalan No</th>
                <th>Purchase Date</th>
                <th style="text-align: right;">Total Quantity</th>
                <th style="text-align: right;">Grand Total (৳)</th>
                <th style="text-align: right;">Paid (৳)</th>
                <th style="text-align: right;">Chalan Due (৳)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $p)
            <tr>
                <td style="font-family: monospace; font-weight: 700;">{{ $p->chalan_no }}</td>
                <td style="font-family: monospace;">{{ $p->purchase_date->format('d/m/Y') }}</td>
                <td style="text-align: right; font-family: monospace;">{{ number_format($p->total_quantity, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($p->grand_total, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d; font-weight: 600;">৳{{ number_format($p->paid_amount, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c; font-weight: 700;">৳{{ number_format($p->due_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align: center;">কোনো ক্রয়ের চালান নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 800;">
                <td colspan="3" style="text-align: right;">সর্বমোট (Totals):</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalPurchased, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d;">৳{{ number_format($totalPaid, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c;">৳{{ number_format($totalDue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endif

<script>
function searchSuppliersAjax(query) {
    const results = document.getElementById('supplier_search_results');
    if (!query || query.length < 2) {
        results.style.display = 'none';
        return;
    }

    fetch(`/purchases/search-suppliers?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                results.innerHTML = data.map(s => `
                    <div onclick="selectSupplier('${s.id}')" style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #1e293b; color: #fff;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='transparent'">
                        <strong style="font-size: 0.85rem; color: #38bdf8;">${s.name}</strong>
                        <div style="font-size: 0.75rem; color: #94a3b8;">${s.company_name || ''} | ${s.phone}</div>
                    </div>
                `).join('');
                results.style.display = 'block';
            } else {
                results.style.display = 'none';
            }
        });
}

function selectSupplier(id) {
    document.getElementById('supplier_dropdown').value = id;
    document.getElementById('supplier_search_results').style.display = 'none';
    document.getElementById('supplierFilterForm').submit();
}
</script>
@endsection