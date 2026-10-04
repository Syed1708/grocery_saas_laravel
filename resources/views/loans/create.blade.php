@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add Loan / Hawlat')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('loans.index') }}">Loans</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Loan / Hawlat (ঋণ বা হাওলাত এন্ট্রি)</h1>
            <p class="page-description">Record borrowing money into the business or lending money out.</p>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('loans.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="type">Loan Type (ধরন) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="taken" {{ old('type') == 'taken' ? 'selected' : '' }}>Loan Taken (দোকানের জন্য ঋণ গ্রহণ - Inflow)</option>
                        <option value="given" {{ old('type') == 'given' ? 'selected' : '' }}>Loan Given (অন্যকে ধার প্রদান - Outflow)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="person_name">Person / Lender Name <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="person_name" name="person_name" class="form-input" placeholder="e.g. জনাব কাশেম সাহেব / আশা সমিতি" value="{{ old('person_name') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="amount">Loan Amount (টাকার পরিমাণ ৳) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-input" placeholder="0.00" value="{{ old('amount') }}" required style="font-family: monospace; font-weight: 700;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="account_id">Impact Account (টাকা গ্রহণ/প্রদানের হিসাব) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <select id="account_id" name="account_id" class="form-select" required>
                        <option value="">অ্যাকাউন্ট নির্বাচন করুন...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }} (ব্যালেন্স: ৳{{ number_format($acc->current_balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="loan_date">Loan Date (তারিখ) <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="date" id="loan_date" name="loan_date" class="form-input" value="{{ old('loan_date', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="due_date">Target Repayment Date (পরিশোধের শেষ তারিখ)</label>
                    <input type="date" id="due_date" name="due_date" class="form-input" value="{{ old('due_date') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-input" placeholder="017XXXXXXXX" value="{{ old('phone') }}">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="notes">Notes / Purpose (মন্তব্য)</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2" placeholder="ঋণের শর্ত বা বিবরণ...">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('loans.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Loan Record</button>
            </div>
        </form>
    </div>
</div>
@endsection