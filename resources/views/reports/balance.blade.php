@extends('tyro-dashboard::layouts.admin')

@section('title', 'Balance Report')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Reports</span>
<span class="breadcrumb-separator">/</span>
<span>Balance Report</span>
@endsection

@section('content')
<div class="page-header no-print">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Daily Balance & Cash Sheet (দৈনিক ক্যাশ ও আর্থিক ব্যালেন্স খতিয়ান)</h1>
            <p class="page-description">Complete daily sales, collections, paid purchases, expenses, and total cash in hand.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Balance Report</button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card filter-card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('reports.balance') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-input">
            </div>
            <div class="form-group" style="margin: 0; min-width: 160px;">
                <label class="form-label" style="font-size: 0.75rem;">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Generate Report</button>
        </form>
    </div>
</div>

<!-- Printable Area -->
<div class="card" id="printable-report" style="padding: 1.5rem;">
    @include('reports.partials.header', ['reportTitle' => 'Daily Financial & Balance Sheet (আর্থিক ব্যালেন্স রিপোর্ট)'])

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1rem;">
        
        <!-- Left: Sales & Receipts -->
        <div>
            <h3 style="font-size: 0.95rem; font-weight: 800; border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 8px;">
                ১. বিক্রয় ও আদায় বিবরণী (Sales & Receipts)
            </h3>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>বিবরণ (Sales Particulars)</th>
                        <th style="text-align: right;">পরিমাণ (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>নগদ বিক্রয় (Cash Sales)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600;">৳{{ number_format($cashSales, 2) }}</td>
                    </tr>
                    <tr>
                        <td>বিকাশ বিক্রয় (bKash Sales)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600;">৳{{ number_format($bkashSales, 2) }}</td>
                    </tr>
                    <tr>
                        <td>নগদ / রকেট বিক্রয় (Nagad / Rocket Sales)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600;">৳{{ number_format($nagadSales, 2) }}</td>
                    </tr>
                    <tr>
                        <td>ব্যাংক কার্ড / ট্রান্সফার বিক্রয় (Bank Sales)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600;">৳{{ number_format($bankSales, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #b91c1c;">বাকি বিক্রয় (Due Sales)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">৳{{ number_format($dueSales, 2) }}</td>
                    </tr>
                    <tr style="background: #f8fafc; font-weight: 800;">
                        <td>সর্বমোট বিক্রয় (Total Sales)</td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.95rem;">৳{{ number_format($totalSales, 2) }}</td>
                    </tr>

                    <tr style="border-top: 2px solid #000; background: #f1f5f9;">
                        <th colspan="2">আদায় ও অন্যান্য আয় (Collections & Incomes)</th>
                    </tr>
                    <tr>
                        <td>কাস্টমার বকেয়া আদায় (Due Collection)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #15803d;">+ ৳{{ number_format($dueCollection, 2) }}</td>
                    </tr>
                    <tr>
                        <td>অন্যান্য আয় (Other Incomes / বস্তা-স্ক্র্যাপ বিক্রি)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #15803d;">+ ৳{{ number_format($otherIncome, 2) }}</td>
                    </tr>
                    <tr style="background: #e2e8f0; font-weight: 900;">
                        <td>মোট নগদ প্রাপ্তি (Total Receipts)</td>
                        <td style="text-align: right; font-family: monospace; font-size: 1rem; color: #15803d;">৳{{ number_format($totalReceipts, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Right: Purchases & Payments -->
        <div>
            <h3 style="font-size: 0.95rem; font-weight: 800; border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 8px;">
                ২. ক্রয় ও পরিশোধ বিবরণী (Purchases & Payments)
            </h3>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>বিবরণ (Payment Particulars)</th>
                        <th style="text-align: right;">পরিমাণ (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>নগদ মাল ক্রয় (Paid Purchase)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">৳{{ number_format($paidPurchase, 2) }}</td>
                    </tr>
                    <tr>
                        <td>বাকি মাল ক্রয় (Due Purchase)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600;">৳{{ number_format($duePurchase, 2) }}</td>
                    </tr>
                    <tr>
                        <td>মহাজন বাকি পরিশোধ (Due Paid)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">৳{{ number_format($duePaid, 2) }}</td>
                    </tr>
                    <tr>
                        <td>দৈনিক দোকান খরচ (Expense)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">৳{{ number_format($expense, 2) }}</td>
                    </tr>
                    <tr>
                        <td>কর্মচারী বেতন, বোনাস ও অগ্রিম (Salary & Overtime)</td>
                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #b91c1c;">৳{{ number_format($salaryExpense, 2) }}</td>
                    </tr>
                    <tr style="background: #fee2e2; font-weight: 800;">
                        <td>মোট খরচ ও পরিশোধ (Total Payments)</td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #b91c1c;">৳{{ number_format($totalPayments, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Grand In-Hand Summary Card -->
            <div style="border: 2px solid #000; background: #f8fafc; padding: 12px; border-radius: 6px; margin-top: 16px;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569;">
                    হিসাব সূত্র: Total In Hand = Total Receipts - Paid Purchase - Due Paid - Expense - Salary
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                    <strong style="font-size: 1.1rem; color: #000;">হাতে নগদ স্থিতি (Total In Hand):</strong>
                    <strong style="font-size: 1.4rem; font-family: monospace; color: {{ $totalInHand >= 0 ? '#15803d' : '#b91c1c' }};">
                        ৳{{ number_format($totalInHand, 2) }}
                    </strong>
                </div>
            </div>
        </div>

    </div>

    @include('reports.partials.footer')
</div>

@include('reports.partials.print-styles')
@endsection