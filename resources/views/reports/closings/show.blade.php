@extends('tyro-dashboard::layouts.admin')

@section('title', 'Z-Report Summary')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('closings.index') }}">Z-Reports</a>
<span class="breadcrumb-separator">/</span>
<span>Closing #{{ $closing->id }}</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Day-End Z-Report (কাউন্টার ক্যাশ ক্লোজিং রশিদ)</h1>
            <p class="page-description">Official day-end shift settlement, physical drawer count, and variance audit.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Z-Report</button>
            <a href="{{ route('closings.index') }}" class="btn btn-secondary">← Back to List</a>
        </div>
    </div>
</div>

<div class="card" id="printable-report" style="max-width: 800px; margin: 0 auto; padding: 1.5rem;">
    <!-- Standard Reusable Header -->
    @include('reports.partials.header', [
        'reportTitle' => 'Day-End Cashier Z-Report (কাউন্টার ক্যাশ ক্লোজিং ও অডিট)',
        'fromDate' => $closing->closing_date->toDateString(),
        'toDate' => $closing->closing_date->toDateString()
    ])

    <!-- Cashier & Time Metadata -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 0.85rem;">
        <div>
            <span style="color: #64748b; font-size: 0.75rem;">Closed By (ক্যাশিয়ার):</span>
            <strong style="display: block; color: #000;">{{ $closing->user?->name ?? 'Admin' }}</strong>
        </div>
        <div>
            <span style="color: #64748b; font-size: 0.75rem;">Closing Date & Time:</span>
            <strong style="display: block; font-family: monospace; color: #000;">{{ $closing->closing_date->format('d/m/Y') }} ({{ date('h:i A', strtotime($closing->closing_time)) }})</strong>
        </div>
        <div>
            <span style="color: #64748b; font-size: 0.75rem;">Total Invoices (মেমো সংখ্যা):</span>
            <strong style="display: block; font-family: monospace; color: #000;">{{ $closing->total_invoices }} Invoices</strong>
        </div>
        <div style="text-align: right;">
            <span style="color: #64748b; font-size: 0.75rem;">Closing Status:</span>
            <strong style="display: block; text-transform: uppercase; color: #15803d;">LOCKED & CLOSED</strong>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
        <!-- Inflows -->
        <div>
            <h4 style="font-size: 0.9rem; font-weight: 800; border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 8px;">
                ১. মোট নগদ প্রাপ্তি (Cash Inflows)
            </h4>
            <table class="table" style="width: 100%;">
                <tbody>
                    <tr>
                        <td>প্রারম্ভিক ক্যাশ (Opening Drawer Cash)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600;">৳{{ number_format($closing->opening_cash, 2) }}</td>
                    </tr>
                    <tr>
                        <td>নগদ বিক্রয় (Cash Sales Today)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #15803d;">+ ৳{{ number_format($closing->cash_sales, 2) }}</td>
                    </tr>
                    <tr>
                        <td>কাস্টমার বাকি আদায় (Due Collected)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #15803d;">+ ৳{{ number_format($closing->cash_due_collected, 2) }}</td>
                    </tr>
                    <tr>
                        <td>অন্যান্য নগদ আয় (Other Income)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #15803d;">+ ৳{{ number_format($closing->cash_other_income, 2) }}</td>
                    </tr>
                    <tr style="background: #f1f5f9; font-weight: 800;">
                        <td>সর্বমোট নগদ আগমন (Total Cash In)</td>
                        <td style="text-align: right; font-family: monospace; color: #15803d;">
                            ৳{{ number_format($closing->opening_cash + $closing->cash_sales + $closing->cash_due_collected + $closing->cash_other_income, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Outflows -->
        <div>
            <h4 style="font-size: 0.9rem; font-weight: 800; border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 8px;">
                ২. মোট নগদ প্রদান / খরচ (Cash Outflows)
            </h4>
            <table class="table" style="width: 100%;">
                <tbody>
                    <tr>
                        <td>দৈনিক দোকান খরচ (Cash Expenses)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">- ৳{{ number_format($closing->cash_expenses, 2) }}</td>
                    </tr>
                    <tr>
                        <td>মহাজন বাকি পরিশোধ (Supplier Paid)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">- ৳{{ number_format($closing->cash_supplier_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <td>পণ্য ফেরত রিফান্ড (Cash Refunds)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">- ৳{{ number_format($closing->cash_refunds, 2) }}</td>
                    </tr>
                    <tr style="background: #fee2e2; font-weight: 800;">
                        <td>সর্বমোট নগদ প্রদান (Total Cash Out)</td>
                        <td style="text-align: right; font-family: monospace; color: #b91c1c;">
                            - ৳{{ number_format($closing->cash_expenses + $closing->cash_supplier_paid + $closing->cash_refunds, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Final Drawer Count & Variance Reconciliation Box -->
    <div style="border: 2px solid #000; background: #f8fafc; border-radius: 6px; padding: 14px; margin-top: 10px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; text-align: center;">
            <div>
                <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">প্রত্যাশিত ক্যাশ (Expected)</span>
                <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: #0284c7; margin-top: 4px;">
                    ৳{{ number_format($closing->expected_cash, 2) }}
                </strong>
            </div>

            <div style="border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;">
                <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">প্রকৃত গোনা ক্যাশ (Counted)</span>
                <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: #000; margin-top: 4px;">
                    ৳{{ number_format($closing->counted_cash, 2) }}
                </strong>
            </div>

            <div>
                <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">পার্থক্য (Variance)</span>
                <strong style="display: block; font-size: 1.25rem; font-family: monospace; color: {{ $closing->difference == 0 ? '#15803d' : ($closing->difference > 0 ? '#0284c7' : '#b91c1c') }}; margin-top: 4px;">
                    {{ $closing->difference >= 0 ? '+' : '' }}৳{{ number_format($closing->difference, 2) }}
                </strong>
            </div>
        </div>

        @if($closing->notes)
            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #cbd5e1; font-size: 0.8rem; color: #475569;">
                <strong>Closing Notes:</strong> {{ $closing->notes }}
            </div>
        @endif
    </div>

    <!-- Perfect Aligned 3-Signature Footer -->
    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection