@extends('tyro-dashboard::layouts.admin')

@section('title', 'Perform Day-End Closing')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('closings.index') }}">Z-Reports</a>
<span class="breadcrumb-separator">/</span>
<span>Day-End Closing</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Day-End Counter Closing (কাউন্টার ক্যাশ ক্লোজিং - Z-Report)</h1>
            <p class="page-description">Reconcile today's cash drawer balance and perform shift settlement.</p>
        </div>
        <a href="{{ route('closings.index') }}" class="btn btn-secondary">← Back to List</a>
    </div>
</div>

<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('closings.store') }}" method="POST">
            @csrf
            <input type="hidden" name="closing_date" value="{{ $today }}">
            <input type="hidden" name="total_invoices" value="{{ $totalInvoices }}">

            <!-- Automated Breakdown Table -->
            <div style=" border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 700; margin-bottom: 1rem; color: var(--foreground);">
                    আজকের ক্যাশ লেনদেন হিসাব (Today: {{ date('d M, Y') }})
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem;">
                    <div style="color: var(--muted-foreground);">Opening Counter Cash (প্রারম্ভিক ক্যাশ):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700;">৳{{ number_format($openingCash, 2) }}</div>
                    <input type="hidden" name="opening_cash" value="{{ $openingCash }}">

                    <div style="color: var(--success, #10b981);">( + ) Cash Sales Today (নগদ বিক্রয়):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">+ ৳{{ number_format($cashSales, 2) }}</div>
                    <input type="hidden" name="cash_sales" value="{{ $cashSales }}">

                    <div style="color: var(--success, #10b981);">( + ) Due Collections in Cash (বাকি আদায়):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">+ ৳{{ number_format($cashDueCollected, 2) }}</div>
                    <input type="hidden" name="cash_due_collected" value="{{ $cashDueCollected }}">

                    <div style="color: var(--success, #10b981);">( + ) Other Incomes in Cash (অন্যান্য আয়):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">+ ৳{{ number_format($cashOtherIncome, 2) }}</div>
                    <input type="hidden" name="cash_other_income" value="{{ $cashOtherIncome }}">

                    <div style="color: var(--danger, #ef4444);">( - ) Cash Expenses (নগদ খরচ):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700; color: var(--danger, #ef4444);">- ৳{{ number_format($cashExpenses, 2) }}</div>
                    <input type="hidden" name="cash_expenses" value="{{ $cashExpenses }}">

                    <div style="color: var(--danger, #ef4444);">( - ) Supplier Cash Paid (মহাজন পরিশোধ):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700; color: var(--danger, #ef4444);">- ৳{{ number_format($cashSupplierPaid, 2) }}</div>
                    <input type="hidden" name="cash_supplier_paid" value="{{ $cashSupplierPaid }}">

                    <div style="color: var(--danger, #ef4444);">( - ) Cash Refunds (ফেরত রিফান্ড):</div>
                    <div style="text-align: right; font-family: monospace; font-weight: 700; color: var(--danger, #ef4444);">- ৳{{ number_format($cashRefunds, 2) }}</div>
                    <input type="hidden" name="cash_refunds" value="{{ $cashRefunds }}">
                </div>

                <div style="border-top: 2px dashed var(--border); margin-top: 1rem; padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-size: 1rem; color: var(--foreground);">ড্রয়ারে মোট প্রত্যাশিত ক্যাশ (Expected Cash):</strong>
                    <strong style="font-size: 1.25rem; font-family: monospace; color: var(--primary, #0284c7);">৳{{ number_format($expectedCash, 2) }}</strong>
                    <input type="hidden" name="expected_cash" id="expected_cash" value="{{ $expectedCash }}">
                </div>
            </div>

            <!-- Cashier Physical Count Input -->
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" for="counted_cash" style="font-size: 0.95rem; font-weight: 700;">
                    ড্রয়ারের প্রকৃত গোনা ক্যাশ (Physical Counted Cash ৳) <span style="color: var(--danger, #ef4444);">*</span>
                </label>
                <input type="number" step="0.01" id="counted_cash" name="counted_cash" class="form-input" placeholder="0.00" required style="font-size: 1.25rem; font-family: monospace; font-weight: 800; padding: 0.75rem;" oninput="calculateDifference()">
            </div>

            <!-- Variance Display -->
            <div id="variance_box" style="display: none; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-weight: 700; font-size: 0.875rem;">
                পার্থক্য / ভ্যারিয়েন্স: <span id="variance_amount" style="font-family: monospace;">৳0.00</span>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="notes">Closing Notes / Comments (ক্লোজিং নোট)</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2" placeholder="ক্যাশ শর্ট বা অতিরিক্ত টাকার কারণ বা মন্তব্য..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('closings.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-weight: 700;">
                    🔒 Lock & Submit Z-Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function calculateDifference() {
    const expected = parseFloat(document.getElementById('expected_cash').value) || 0;
    const counted = parseFloat(document.getElementById('counted_cash').value) || 0;
    const diff = counted - expected;
    const box = document.getElementById('variance_box');
    const amount = document.getElementById('variance_amount');

    box.style.display = 'block';
    if (diff === 0) {
        box.style.background = 'rgba(16, 185, 129, 0.15)';
        box.style.color = '#10b981';
        amount.innerText = '৳0.00 (হিসাব ১০০% সঠিক)';
    } else if (diff > 0) {
        box.style.background = 'rgba(2, 132, 199, 0.15)';
        box.style.color = '#38bdf8';
        amount.innerText = '+ ৳' + diff.toFixed(2) + ' (ক্যাশ অতিরিক্ত/Surplus)';
    } else {
        box.style.background = 'rgba(239, 68, 68, 0.15)';
        box.style.color = '#ef4444';
        amount.innerText = '- ৳' + Math.abs(diff).toFixed(2) + ' (ক্যাশ ঘাটতি/Shortage)';
    }
}
</script>
@endsection