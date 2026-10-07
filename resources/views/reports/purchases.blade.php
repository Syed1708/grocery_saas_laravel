@extends('tyro-dashboard::layouts.admin')

@section('title', 'Purchase Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Purchase Report</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Purchase Report (ক্রয় চালান রিপোর্ট)</h1>
            <p class="page-description">Procurement logs, grand totals, and supplier outstanding liabilities.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Purchases</button>
    </div>
</div>

<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('reports.purchases') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
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
    @include('reports.partials.header', ['reportTitle' => 'Procurement & Purchase Report (মাল ক্রয় রিপোর্ট)'])

    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Chalan No</th>
                <th>Purchase Date</th>
                <th>Supplier / Dealer</th>
                <th>Company</th>
                <th style="text-align: right;">Total Qty</th>
                <th style="text-align: right;">Grand Total (৳)</th>
                <th style="text-align: right;">Paid (৳)</th>
                <th style="text-align: right;">Due (৳)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $p)
            <tr>
                <td style="font-family: monospace; font-weight: 700;">{{ $p->chalan_no }}</td>
                <td style="font-family: monospace;">{{ $p->purchase_date->format('d/m/Y') }}</td>
                <td><strong>{{ $p->supplier?->name }}</strong></td>
                <td>{{ $p->supplier?->company_name ?? '—' }}</td>
                <td style="text-align: right; font-family: monospace;">{{ number_format($p->total_quantity, 2) }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($p->grand_total, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d; font-weight: 600;">৳{{ number_format($p->paid_amount, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c; font-weight: 700;">৳{{ number_format($p->due_amount, 2) }}</td>
                <td style="text-transform: uppercase;">{{ $p->payment_status }}</td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align: center;">কোনো ক্রয়ের তথ্য নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: var(--muted, rgba(148, 163, 184, 0.05)); font-weight: 800;">
                <td colspan="5" style="text-align: right;">মোট হিসাব (Grand Totals):</td>
                <td style="text-align: right; font-family: monospace;">৳{{ number_format($totalGrand, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #15803d;">৳{{ number_format($totalPaid, 2) }}</td>
                <td style="text-align: right; font-family: monospace; color: #b91c1c;">৳{{ number_format($totalDue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection