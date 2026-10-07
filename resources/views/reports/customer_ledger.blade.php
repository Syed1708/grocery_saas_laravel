@extends('tyro-dashboard::layouts.admin')

@section('title', 'Customer Ledger Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Customer Ledger</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Customer Ledger Report (কাস্টমার খতিয়ান ও বাকি হিসাব)</h1>
            <p class="page-description">Search or select customer to inspect sales invoices, due collections, and balance history.</p>
        </div>
        @if($selectedCustomer)
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Customer Ledger</button>
        @endif
    </div>
</div>

<!-- Dual Search & Filter Card -->
<div class="card filter-card no-print" style="margin-bottom: 1.5rem; border: 1px solid var(--border);">
    <div class="card-body" style="padding: 1.25rem;">
        <form method="GET" action="{{ route('reports.customer-ledger') }}" id="customerFilterForm">
            <div style="display: grid; grid-template-columns: 1.5fr 1.2fr 1fr 1fr auto; gap: 0.85rem; align-items: flex-end;">
                
                <!-- Option 1: Live Type Search Input with Auto-Fill Suggestions -->
                <div class="form-group" style="margin: 0; position: relative;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">🔍 Search Customer (নাম বা মোবাইল)</label>
                    <input type="text" id="customer_search_input" 
                           placeholder="টাইপ করে কাস্টমার খুঁজুন..." 
                           class="form-input" 
                           value="{{ $selectedCustomer ? $selectedCustomer->name . ' (' . $selectedCustomer->phone . ')' : '' }}" 
                           autocomplete="off" 
                           oninput="searchCustomersAjax(this.value)"
                           style="height: 38px; font-size: 12px;">
                    
                    <!-- Suggestions Dropdown Menu -->
                    <div id="customer_search_results" style="display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 1000; background: var(--card, #1e293b); border: 1px solid var(--border, #334155); border-radius: 8px; max-height: 220px; overflow-y: auto; box-shadow: 0 14px 28px rgba(0,0,0,0.5); margin-top: 4px;"></div>
                </div>

                <!-- Option 2: Select from List Dropdown -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">📋 Select from Dropdown <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select name="customer_id" id="customer_dropdown" class="form-select" required onchange="onCustomerDropdownChange(this)" style="height: 38px; font-size: 12px;">
                        <option value="">কাস্টমার নির্বাচন করুন...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" 
                                    data-name="{{ $c->name }}" 
                                    data-phone="{{ $c->phone }}" 
                                    data-due="{{ $c->current_due }}"
                                    {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }}) - বকেয়া: ৳{{ number_format($c->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
                    <a href="{{ route('reports.customer-ledger') }}" class="btn btn-secondary" style="height: 38px; padding: 0 12px; display: flex; align-items: center;" title="Reset">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedCustomer)
<div class="card" id="printable-report" style="padding: 1.5rem; border: 1px solid var(--border);">
    @include('reports.partials.header', [
        'reportTitle' => 'Customer Account Statement: ' . $selectedCustomer->name . ' (' . $selectedCustomer->phone . ')'
    ])

    <!-- Customer Overview Banner (Dark & Light Theme Adaptive) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; background: var(--muted, rgba(148, 163, 184, 0.08)); border: 1px solid var(--border); padding: 12px; border-radius: 8px; margin-bottom: 16px;">
        <div>
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Customer Name</span>
            <strong style="display: block; font-size: 0.95rem; color: var(--foreground);">{{ $selectedCustomer->name }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Phone Number</span>
            <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: var(--foreground);">{{ $selectedCustomer->phone }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Credit Limit (বাকির সীমা)</span>
            <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: var(--foreground);">৳{{ number_format($selectedCustomer->credit_limit, 2) }}</strong>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase;">Current Due (মোট বকেয়া)</span>
            <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: var(--danger, #ef4444);">৳{{ number_format($selectedCustomer->current_due, 2) }}</strong>
        </div>
    </div>

    <!-- Sales Invoices -->
    <h3 style="font-size: 0.9rem; font-weight: 800; border-bottom: 1px solid var(--border); padding-bottom: 4px; margin-top: 10px; margin-bottom: 8px; color: var(--foreground);">
        ১. বিক্রয় ইনভয়েস তালিকা (Sales Invoices)
    </h3>
    <div class="table-container">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th style="text-align: right;">Grand Total (৳)</th>
                    <th style="text-align: right;">Paid (৳)</th>
                    <th style="text-align: right;">Invoice Due (৳)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td style="font-family: monospace; font-weight: 700;">{{ $s->invoice_no }}</td>
                    <td style="font-family: monospace;">{{ $s->sale_date->format('d/m/Y') }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($s->grand_total, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--success, #10b981); font-weight: 600;">৳{{ number_format($s->paid_amount, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--danger, #ef4444); font-weight: 700;">৳{{ number_format($s->due_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted-foreground);">কোনো বিক্রয় রেকর্ড নেই।</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: var(--muted, rgba(148, 163, 184, 0.08)); font-weight: 800;">
                    <td colspan="2" style="text-align: right;">মোট বিক্রয়:</td>
                    <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalSales, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--success, #10b981);">৳{{ number_format($totalPaid, 2) }}</td>
                    <td style="text-align: right; font-family: monospace; color: var(--danger, #ef4444);">৳{{ number_format($totalDue, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Direct Due Collections -->
    <h3 style="font-size: 0.9rem; font-weight: 800; border-bottom: 1px solid var(--border); padding-bottom: 4px; margin-top: 20px; margin-bottom: 8px; color: var(--foreground);">
        ২. বকেয়া পরিশোধ / আদায় রসিদ (Due Collection Receipts)
    </h3>
    <div class="table-container">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Reference</th>
                    <th style="text-align: right;">Amount Paid (৳)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pm)
                <tr>
                    <td style="font-family: monospace; font-weight: 700;">{{ $pm->receipt_no }}</td>
                    <td style="font-family: monospace;">{{ $pm->payment_date->format('d/m/Y') }}</td>
                    <td style="text-transform: uppercase;">{{ $pm->payment_method }}</td>
                    <td>{{ $pm->reference ?? '—' }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">
                        ৳{{ number_format($pm->amount, 2) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted-foreground);">আলাদা কোনো বাকি আদায়ের রসিদ নেই।</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: var(--muted, rgba(148, 163, 184, 0.08)); font-weight: 800;">
                    <td colspan="4" style="text-align: right;">মোট আদায়কৃত বকেয়া (Total Collected):</td>
                    <td style="text-align: right; font-family: monospace; color: var(--success, #10b981);">৳{{ number_format($totalCollected, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endif

<!-- JAVASCRIPT: Adaptive Search & Auto-Select -->
<script>
let customerSearchTimer;

function searchCustomersAjax(query) {
    clearTimeout(customerSearchTimer);
    const results = document.getElementById('customer_search_results');

    if (!query || query.trim().length < 2) {
        results.style.display = 'none';
        return;
    }

    customerSearchTimer = setTimeout(() => {
        fetch(`{{ route('pos.api.customers') }}?q=${encodeURIComponent(query.trim())}`)
            .then(res => res.json())
            .then(data => {
                results.innerHTML = '';
                if (!data || data.length === 0) {
                    results.innerHTML = '<div style="padding: 10px 14px; font-size: 11px; color: var(--muted-foreground);">কোনো কাস্টমার পাওয়া যায়নি।</div>';
                } else {
                    data.forEach(c => {
                        const item = document.createElement('div');
                        item.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--border); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                        item.onmouseover = () => item.style.background = 'var(--muted, #334155)';
                        item.onmouseout = () => item.style.background = 'transparent';
                        item.innerHTML = `
                            <div>
                                <strong style="color: var(--foreground);">${c.name}</strong>
                                <div style="font-size: 10px; color: var(--muted-foreground);">${c.phone}</div>
                            </div>
                            <span class="badge ${c.current_due > 0 ? 'badge-danger' : 'badge-success'}" style="font-size: 10px;">বকেয়া: ৳${parseFloat(c.current_due).toFixed(2)}</span>
                        `;
                        item.onclick = () => selectCustomerFromAjax(c);
                        results.appendChild(item);
                    });
                }
                results.style.display = 'block';
            })
            .catch(() => results.style.display = 'none');
    }, 200);
}

function selectCustomerFromAjax(c) {
    const dropdown = document.getElementById('customer_dropdown');
    dropdown.value = c.id;
    document.getElementById('customer_search_input').value = `${c.name} (${c.phone})`;
    document.getElementById('customer_search_results').style.display = 'none';
    document.getElementById('customerFilterForm').submit();
}

function onCustomerDropdownChange(select) {
    const opt = select.options[select.selectedIndex];
    if (select.value) {
        const name = opt.getAttribute('data-name');
        const phone = opt.getAttribute('data-phone');
        document.getElementById('customer_search_input').value = `${name} (${phone})`;
        document.getElementById('customerFilterForm').submit();
    } else {
        document.getElementById('customer_search_input').value = '';
    }
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('#customer_search_input') && !e.target.closest('#customer_search_results')) {
        document.getElementById('customer_search_results').style.display = 'none';
    }
});
</script>
@endsection