@extends('tyro-dashboard::layouts.admin')

@section('title', 'Process Sale Return')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('returns.index') }}">Returns</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Process Sale Return (পণ্য ফেরত)</h1>
            <p class="page-description">Lookup original sale invoice to select returned items.</p>
        </div>
        <a href="{{ route('returns.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form action="{{ route('returns.create') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end;">
            <div class="form-group" style="flex: 1;">
                <label class="form-label" for="invoice_no">Enter Sale Invoice No (চালান / মেমো নং)</label>
                <input type="text" id="invoice_no" name="invoice_no" class="form-input" placeholder="e.g. INV-260901-1234" value="{{ request('invoice_no') }}" required style="font-family: monospace;">
            </div>
            <button type="submit" class="btn btn-primary">Search Invoice</button>
        </form>
    </div>
</div>

@if($sale)
<form action="{{ route('returns.store') }}" method="POST">
    @csrf
    <input type="hidden" name="sale_id" value="{{ $sale->id }}">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
            <div>
                <strong>Invoice #{{ $sale->invoice_no }}</strong> • Date: {{ $sale->sale_date->format('d M, Y') }}
                <div style="font-size: 12px; color: var(--muted-foreground);">Customer: {{ $sale->customer?->name ?? 'Cash Customer' }}</div>
            </div>
            <div style="text-align: right;">
                <span class="badge badge-primary">Total Sold: ৳{{ number_format($sale->grand_total, 2) }}</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold Qty</th>
                        <th>Unit Price</th>
                        <th style="width: 140px;">Return Qty</th>
                        <th style="width: 160px;">Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $it)
                    <tr>
                        <td>
                            <strong>{{ $it->product->name }}</strong>
                            <input type="hidden" name="items[{{ $it->product_id }}][unit_id]" value="{{ $it->unit_id }}">
                            <input type="hidden" name="items[{{ $it->product_id }}][price]" value="{{ $it->unit_price }}">
                        </td>
                        <td>{{ $it->quantity }} {{ $it->unit?->short_code }}</td>
                        <td>৳{{ number_format($it->unit_price, 2) }}</td>
                        <td>
                            <input type="number" step="0.1" name="items[{{ $it->product_id }}][return_qty]" class="form-input" placeholder="0" max="{{ $it->quantity }}" style="height: 28px;">
                        </td>
                        <td>
                            <select name="items[{{ $it->product_id }}][condition]" class="form-select" style="height: 28px; font-size: 11px;">
                                <option value="restock">Restock (ভালো পণ্য)</option>
                                <option value="damaged">Damaged (নষ্ট/ক্ষতি)</option>
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="max-width: 500px; margin-left: auto;">
        <div class="card-body" style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Refund Method</label>
                <select name="refund_method" class="form-select">
                    <option value="cash">ক্যাশ রিফান্ড (Cash Refund)</option>
                    <option value="balance">বাকি সমন্বয় (Customer Balance Credit)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Reason for Return</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="e.g. অতিরিক্ত কেনা হয়েছিল / প্যাকেট ছেঁড়া"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm Return & Refund</button>
        </div>
    </div>
</form>
@elseif(request()->filled('invoice_no'))
<div class="card">
    <div class="card-body" style="text-align: center; padding: 2rem; color: var(--danger, #ef4444);">
        ❌ কোনো বিক্রয় চালান পাওয়া যায়নি। অনুগ্রহ করে সঠিক চালান নম্বর লিখুন।
    </div>
</div>
@endif
@endsection