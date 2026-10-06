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
<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1.25rem;">
        <form method="GET" action="{{ route('reports.customer-ledger') }}" id="customerFilterForm">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: flex-end;">
                
                <!-- Option 1: Live Type Search -->
                <div class="form-group" style="margin: 0; position: relative;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">🔍 Option 1: Search Customer (নাম বা ফোন)</label>
                    <input type="text" id="customer_search_input" placeholder="টাইপ করে কাস্টমার খুঁজুন..." class="form-input" autocomplete="off" oninput="searchCustomersAjax(this.value)">
                    <div id="customer_search_results" style="display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 50; background: #0f172a; border: 1px solid #334155; border-radius: 8px; max-height: 200px; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5);"></div>
                </div>

                <!-- Option 2: Select Dropdown -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 700;">📋 Option 2: Select from List <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select name="customer_id" id="customer_dropdown" class="form-select" required onchange="document.getElementById('customerFilterForm').submit()">
                        <option value="">কাস্টমার নির্বাচন করুন...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }}) - বকেয়া: ৳{{ number_format($c->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

@if($selectedCustomer)
<div class="card" id="printable-report" style="padding: 1.5rem;">
    @include('reports.partials.header', [
        'reportTitle' => 'Customer Account Statement: ' . $selectedCustomer->name . ' (' . $selectedCustomer->phone . ')'
    ])

    <!-- Customer Overview Banner -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
        <div>
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Customer Name</span>
            <strong style="display: block; font-size: 0.95rem; color: #000;">{{ $selectedCustomer->name }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Phone Number</span>
            <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: #000;">{{ $selectedCustomer->phone }}</strong>
        </div>
        <div>
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Credit Limit (বাকির সীমা)</span>
            <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: #000;">৳{{ number_format($selectedCustomer->credit_limit, 2) }}</strong>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Current Due (মোট বকেয়া)</span>
            <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: #b91c1c;">৳{{ number_format($selectedCustomer->current_due, 2) }}</strong>
        </div>
    </div>

    <!-- Sales Invoices -->
    <h3 style="font-size: 0.9rem; font-weight: 800; border-bottom: 1px solid #000; padding-bottom: 4px; margin-top: 10px; margin-bottom: 8px;">
        ১. বিক্রয় ইনভয়েস তালিকা (Sales Invoices)
    </h3>
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
                <td style="text-align: right; font-family: monospace; color: #15803d; font-weight: 600;">৳{{ number_format($s->paid_amount, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c; font-weight: 700;">৳{{ number_format($s->due_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align: center;">কোনো বিক্রয় রেকর্ড নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 800;">
                <td colspan="2" style="text-align: right;">মোট বিক্রয়:</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalSales, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d;">৳{{ number_format($totalPaid, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c;">৳{{ number_format($totalDue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Direct Due Collections -->
    <h3 style="font-size: 0.9rem; font-weight: 800; border-bottom: 1px solid #000; padding-bottom: 4px; margin-top: 20px; margin-bottom: 8px;">
        ২. বকেয়া পরিশোধ / আদায় রসিদ (Due Collection Receipts)
    </h3>
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
                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #15803d;">
                    ৳{{ number_format($pm->amount, 2) }}
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align: center;">আলাদা কোনো বাকি আদায়ের রসিদ নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 800;">
                <td colspan="4" style="text-align: right;">মোট আদায়কৃত বকেয়া (Total Collected):</td>
                <td style="text-align: right; font-family: monospace; color: #15803d;">৳{{ number_format($totalCollected, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endif

<script>
function searchCustomersAjax(query) {
    const results = document.getElementById('customer_search_results');
    if (!query || query.length < 2) {
        results.style.display = 'none';
        return;
    }

    fetch(`/pos/search-customers?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                results.innerHTML = data.map(c => `
                    <div onclick="selectCustomer('${c.id}')" style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #1e293b; color: #fff;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='transparent'">
                        <strong style="font-size: 0.85rem; color: #38bdf8;">${c.name}</strong>
                        <div style="font-size: 0.75rem; color: #94a3b8;">${c.phone} | বকেয়া: ৳${c.current_due}</div>
                    </div>
                `).join('');
                results.style.display = 'block';
            } else {
                results.style.display = 'none';
            }
        });
}

function selectCustomer(id) {
    document.getElementById('customer_dropdown').value = id;
    document.getElementById('customer_search_results').style.display = 'none';
    document.getElementById('customerFilterForm').submit();
}
</script>
@endsection