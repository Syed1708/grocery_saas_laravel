@extends('tyro-dashboard::layouts.admin')

@section('title', 'Supplier Due Paid Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Due Paid</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Supplier Due Paid Report (মহাজন বাকি পরিশোধ রিপোর্ট)</h1>
            <p class="page-description">Record of all payments and debits settled against supplier chalans.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
    </div>
</div>

<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('reports.due-paid') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
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
    @include('reports.partials.header', ['reportTitle' => 'Supplier Due Paid Report (মহাজন বাকি পরিশোধ খতিয়ান)'])

    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher No</th>
                <th>Supplier / Dealer Name</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Method</th>
                <th style="text-align: right;">Paid Amount (৳)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
            <tr>
                <td style="font-family: monospace;">{{ $p->payment_date->format('d/m/Y') }}</td>
                <td style="font-family: monospace; font-weight: 600;">{{ $p->voucher_no }}</td>
                <td><strong>{{ $p->supplier?->name }}</strong></td>
                <td>{{ $p->supplier?->company_name ?? '—' }}</td>
                <td style="font-family: monospace;">{{ $p->supplier?->phone }}</td>
                <td style="text-transform: uppercase;">{{ $p->payment_method }}</td>
                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #b91c1c;">
                    ৳{{ number_format($p->amount, 2) }}
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align: center;">কোনো পরিশোধের রেকর্ড নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 800;">
                <td colspan="6" style="text-align: right;">সর্বমোট পরিশোধ (Total Paid):</td>
                <td style="text-align: right; font-family: monospace; font-size: 1rem; color: #b91c1c;">
                    ৳{{ number_format($totalAmount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection