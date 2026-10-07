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
            <p class="page-description">Search or select supplier to inspect purchase chalans, payments, and balances.</p>
        </div>
        @if($selectedSupplier)
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Supplier Report</button>
        @endif
    </div>
</div>

<!-- Dual Search & Filter Card -->
<div class="card filter-card no-print" style="margin-bottom: 1.5rem; border: 1px solid var(--border);">
    <div class="card-body" style="padding: 1.25rem;">
        <form method="GET" action="{{ route('reports.supplier-purchases') }}" id="supplierFilterForm">
            <div style="display: grid; grid-template-columns: 1.5fr 1.2fr 1fr 1fr auto; gap: 0.85rem; align-items: flex-end;">
                
                <!-- Option 1: Live Type Search Input with Auto-Fill Suggestions -->
                <div class="form-group" style="margin: 0; position: relative;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">🔍 Search Supplier (নাম, কোম্পানি বা মোবাইল)</label>
                    <input type="text" id="supplier_search_input" 
                           placeholder="টাইপ করে মহাজন খুঁজুন..." 
                           class="form-input" 
                           value="{{ $selectedSupplier ? $selectedSupplier->name . ' (' . ($selectedSupplier->company_name ?? $selectedSupplier->phone) . ')' : '' }}" 
                           autocomplete="off" 
                           oninput="searchSuppliersAjax(this.value)"
                           style="height: 38px; font-size: 12px;">
                    
                    <!-- Suggestions Dropdown Menu -->
                    <div id="supplier_search_results" style="display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; background: var(--card, #1e293b); border: 1px solid var(--border, #334155); border-radius: 8px; max-height: 220px; overflow-y: auto; box-shadow: 0 14px 28px rgba(0,0,0,0.5); margin-top: 4px;"></div>
                </div>

                <!-- Option 2: Select from List Dropdown -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">📋 Select from Dropdown <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select name="supplier_id" id="supplier_dropdown" class="form-select" required onchange="onSupplierDropdownChange(this)" style="height: 38px; font-size: 12px;">
                        <option value="">মহাজন নির্বাচন করুন...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" 
                                    data-name="{{ $s->name }}" 
                                    data-company="{{ $s->company_name }}" 
                                    data-phone="{{ $s->phone }}" 
                                    data-due="{{ $s->current_due }}"
                                    {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->company_name ?? $s->phone }}) - বকেয়া: ৳{{ number_format($s->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-input" style="height: 38px; font-size: 12px;">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-input" style="height: 38px; font-size: 12px;">
                </div>

                <div style="display: flex; gap: 4px;">
                    <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px; font-weight: 700; white-space: nowrap;">Show Ledger</button>
                    <a href="{{ route('reports.supplier-purchases') }}" class="btn btn-secondary" style="height: 38px; padding: 0 12px; display: flex; align-items: center;" title="Reset">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedSupplier)
<div class="card" id="printable-report" style="padding: 1.5rem; border: 1px solid var(--border);">
    @include('reports.partials.header', [
        'reportTitle' => 'Supplier Procurement Ledger: ' . $selectedSupplier->name . ' (' . ($selectedSupplier->company_name ?? 'N/A') . ')'
    ])

    <!-- Supplier Overview Banner (Dark & Light Theme Adaptive) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08)); border: 1px solid var(--border); padding: 12px; border-radius: 8px; margin-bottom: 16px;">
        <div>
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Supplier Name</span>
            <strong style="display: block; font-size: 0.95rem; color: var(--foreground);">{{ $selectedSupplier->name }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Company / Agency</span>
            <strong style="display: block; font-size: 0.95rem; color: var(--foreground);">{{ $selectedSupplier->company_name ?? 'N/A' }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Phone Number</span>
            <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: var(--foreground);">{{ $selectedSupplier->phone }}</strong>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Current Due (মোট বকেয়া দেনা)</span>
            <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: var(--danger, #ef4444);">৳{{ number_format($selectedSupplier->current_due, 2) }}</strong>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="table-container">
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
                    <td style="text-align: right; font-family: monospace; color: var(--success, #10b981); font-weight: 600;">৳{{ number_format($p->paid_amount, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--danger, #ef4444); font-weight: 700;">৳{{ number_format($p->due_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--muted-foreground);">কোনো ক্রয়ের চালান নেই।</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: var(--muted, rgba(148, 163, 184, 0.08)); font-weight: 800;">
                    <td colspan="3" style="text-align: right;">সর্বমোট (Totals):</td>
                    <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalPurchased, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--success, #10b981);">৳{{ number_format($totalPaid, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--danger, #ef4444);">৳{{ number_format($totalDue, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Summary Box -->
    <div style="margin-top: 15px; padding: 12px; border: 1px solid var(--border); background: var(--muted, rgba(148, 163, 184, 0.05)); border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <strong style="color: var(--foreground);">মহাজনের সর্বমোট বকেয়া দেনা (Total Current Due):</strong>
        <strong style="font-family: monospace; font-size: 1.25rem; color: var(--danger, #ef4444);">৳{{ number_format($selectedSupplier->current_due, 2) }}</strong>
    </div>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endif

<!-- JAVASCRIPT: Adaptive Search & Auto-Select -->
<script>
let supplierSearchTimer;

function searchSuppliersAjax(query) {
    clearTimeout(supplierSearchTimer);
    const results = document.getElementById('supplier_search_results');

    if (!query || query.trim().length < 2) {
        results.style.display = 'none';
        return;
    }

    supplierSearchTimer = setTimeout(() => {
        fetch(`{{ route('purchases.api.suppliers') }}?q=${encodeURIComponent(query.trim())}`)
            .then(res => res.json())
            .then(data => {
                results.innerHTML = '';
                if (!data || data.length === 0) {
                    results.innerHTML = '<div style="padding: 10px 14px; font-size: 11px; color: var(--muted-foreground);">কোনো মহাজন পাওয়া যায়নি।</div>';
                } else {
                    data.forEach(s => {
                        const item = document.createElement('div');
                        item.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                        item.onmouseover = () => item.style.background = 'var(--muted, #334155)';
                        item.onmouseout = () => item.style.background = 'transparent';
                        item.innerHTML = `
                            <div>
                                <strong style="color: var(--foreground);">${s.name}</strong>
                                <div style="font-size: 10px; color: var(--muted-foreground);">${s.company_name ? s.company_name + ' | ' : ''}${s.phone}</div>
                            </div>
                            <span class="badge ${s.current_due > 0 ? 'badge-danger' : 'badge-success'}" style="font-size: 10px;">বকেয়া: ৳${parseFloat(s.current_due).toFixed(2)}</span>
                        `;
                        item.onclick = () => selectSupplierFromAjax(s);
                        results.appendChild(item);
                    });
                }
                results.style.display = 'block';
            })
            .catch(() => results.style.display = 'none');
    }, 200);
}

function selectSupplierFromAjax(s) {
    const dropdown = document.getElementById('supplier_dropdown');
    dropdown.value = s.id;
    document.getElementById('supplier_search_input').value = `${s.name} (${s.company_name || s.phone})`;
    document.getElementById('supplier_search_results').style.display = 'none';
    document.getElementById('supplierFilterForm').submit();
}

function onSupplierDropdownChange(select) {
    const opt = select.options[select.selectedIndex];
    if (select.value) {
        const name = opt.getAttribute('data-name');
        const comp = opt.getAttribute('data-company');
        const phone = opt.getAttribute('data-phone');
        document.getElementById('supplier_search_input').value = `${name} (${comp || phone})`;
        document.getElementById('supplierFilterForm').submit();
    } else {
        document.getElementById('supplier_search_input').value = '';
    }
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('#supplier_search_input') && !e.target.closest('#supplier_search_results')) {
        document.getElementById('supplier_search_results').style.display = 'none';
    }
});
</script>
@endsection