@extends('tyro-dashboard::layouts.admin')

@section('title', 'Loan Details')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('loans.index') }}">Loans</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $loan->person_name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $loan->person_name }} (ঋণ ও কিস্তি বিবরণী)</h1>
            <p class="page-description">Loan profile, installment repayment ledger, and balance settlement.</p>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-secondary">← Back to Loans</a>
    </div>
</div>

@if(session('success'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--success, #10b981); background: rgba(16, 185, 129, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--success, #10b981); font-weight: 500; font-size: 0.875rem;">
        {{ session('success') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="card" style="margin-bottom: 1rem; border-left: 4px solid var(--danger, #ef4444); background: rgba(239, 68, 68, 0.08);">
    <div class="card-body" style="padding: 0.75rem 1rem; color: var(--danger, #ef4444); font-weight: 500; font-size: 0.875rem;">
        {{ session('error') }}
    </div>
</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;">
    
    <!-- Left: Loan Summary & Repay Form -->
    <div class="space-y-4">
        <div class="card">
            <div class="card-body" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span class="badge {{ $loan->type === 'taken' ? 'badge-danger' : 'badge-success' }}">
                        {{ $loan->type === 'taken' ? 'দোকানের ঋণ (Payable)' : 'অন্যকে দেওয়া ধার (Receivable)' }}
                    </span>
                    <span class="badge {{ $loan->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ ucfirst($loan->status) }}
                    </span>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">মূল ঋণ (Principal)</span>
                    <strong style="display: block; font-size: 1.25rem; font-family: monospace;">৳{{ number_format($loan->amount, 2) }}</strong>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">পরিশোধিত (Total Paid)</span>
                    <strong style="display: block; font-size: 1.125rem; color: var(--success, #10b981); font-family: monospace;">৳{{ number_format($loan->total_paid, 2) }}</strong>
                </div>

                <div style="margin-bottom: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border);">
                    <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">অবশিষ্ট বকেয়া (Remaining Due)</span>
                    <strong style="display: block; font-size: 1.5rem; color: var(--danger, #ef4444); font-family: monospace;">৳{{ number_format($loan->remaining_amount, 2) }}</strong>
                </div>

                <div style="font-size: 0.75rem; color: var(--muted-foreground);">
                    <div>Date: <strong style="color: var(--foreground);">{{ $loan->loan_date->format('d M, Y') }}</strong></div>
                    <div>Phone: <strong style="color: var(--foreground);">{{ $loan->phone ?? 'N/A' }}</strong></div>
                </div>
            </div>
        </div>

        @if($loan->status !== 'paid')
        <!-- Installment Payment Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 0.875rem;">কিস্তি পরিশোধ / জমা এন্ট্রি</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('loans.installments', ['loan' => $loan->id]) }}" method="POST">
                    @csrf

                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Amount (টাকার পরিমাণ ৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                        <input type="number" step="0.01" name="amount" max="{{ $loan->remaining_amount }}" class="form-input" placeholder="0.00" required style="font-family: monospace; font-weight: 700;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Payment Account <span style="color: var(--danger, #ef4444);">*</span></label>
                        <select name="account_id" class="form-select" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (৳{{ number_format($acc->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-input" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Note</label>
                        <input type="text" name="note" class="form-input" placeholder="কিস্তি নং বা বিবরণ...">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        কিস্তির টাকা জমা দিন
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Right: Installment History Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">কিস্তি পরিশোধের ইতিহাস (Installment History)</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Account</th>
                        <th>Method</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installments as $inst)
                    <tr>
                        <td style="font-family: monospace; color: var(--muted-foreground);">
                            {{ $inst->payment_date->format('d M, Y') }}
                        </td>
                        <td style="font-family: monospace; font-size: 0.75rem;">
                            {{ $inst->voucher_no }}
                        </td>
                        <td>
                            {{ $inst->account?->name ?? '—' }}
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="text-transform: uppercase; font-size: 10px;">
                                {{ $inst->payment_method }}
                            </span>
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--success, #10b981);">
                            ৳{{ number_format($inst->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--muted-foreground);">
                            এখনো কোনো কিস্তি পরিশোধ করা হয়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($installments->hasPages())
        <div class="pagination">
            {{ $installments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection