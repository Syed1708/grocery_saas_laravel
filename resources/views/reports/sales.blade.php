@extends('tyro-dashboard::layouts.admin')

@section('title', 'Sales Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Sales Report</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Sales Report (বিক্রয় মেমো রিপোর্ট)</h1>
            <p class="page-description">Detailed list of sales invoices, payments, and remaining customer dues.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Sales Report</button>
    </div>
</div>

<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('reports.sales') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-input">
            </div>
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter</button>
        </form>
    </div>
</div>

<div class="card" id="printable-report" style="padding: 1.5rem;">
    @include('reports.partials.header', ['reportTitle' => 'Sales Invoices Report (বিক্রয় চালান তালিকা)'])

    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Date</th>
                <th>Customer</th>
                <th style="text-align: right;">Subtotal</th>
                <th style="text-align: right;">Discount</th>
                <th style="text-align: right;">Grand Total (৳)</th>
                <th style="text-align: right;">Paid (৳)</th>
                <th style="text-align: right;">Due (৳)</th>
                <th>Method</th>
                <th>Cashier</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $s)
            <tr>
                <td style="font-family: monospace; font-weight: 700;">{{ $s->invoice_no }}</td>
                <td style="font-family: monospace;">{{ $s->sale_date->format('d/m/Y') }}</td>
                <td>{{ $s->customer?->name ?? 'Walk-in Cash' }}</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($s->subtotal, 2) }}</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($s->discount, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($s->grand_total, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d; font-weight: 600;">৳{{ number_format($s->paid_amount, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c; font-weight: 700;">৳{{ number_format($s->due_amount, 2) }}</td>
                <td style="text-transform: uppercase;">{{ $s->payment_method }}</td>
                <td>{{ $s->cashier?->name }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align: center;">কোনো বিক্রয়ের তথ্য নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 800;">
                <td colspan="5" style="text-align: right;">মোট হিসাব (Grand Totals):</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalGrand, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d;">৳{{ number_format($totalPaid, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c;">৳{{ number_format($totalDue, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection