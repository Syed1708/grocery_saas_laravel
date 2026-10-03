@extends('tyro-dashboard::layouts.admin')

@section('title', 'Quotation #' . $quotation->quotation_no)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('quotations.index') }}">Quotations</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $quotation->quotation_no }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Quotation #{{ $quotation->quotation_no }}</h1>
            <p class="page-description">Customer: {{ $quotation->customer?->name ?? 'General Client' }} • Date: {{ $quotation->quotation_date->format('d M, Y') }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($quotation->status === 'pending')
                <form action="{{ route('quotations.convert', ['quotation' => $quotation->id]) }}" method="POST" onsubmit="return confirm('এই দরপত্রটি বিক্রয় ইনভয়েসে রূপান্তর করবেন? স্টক স্বয়ংক্রিয়ভাবে কাটা হবে।')">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-weight: 800; background: var(--success, #10b981);">
                        ⚡ Convert to Live Sale (ইনভয়েসে রূপান্তর)
                    </button>
                </form>
            @endif
            <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Estimate</button>
            <a href="{{ route('quotations.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body" style="padding: 2rem;">
        
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--border); padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--foreground);">{{ \App\Models\ShopSetting::get('shop_name', 'Madina General Store') }}</h2>
                <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 4px;">Branch: {{ $quotation->branch->name }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 1rem; font-weight: 800; color: var(--primary);">PRICE ESTIMATION</div>
                <div style="font-family: monospace; font-size: 0.875rem; font-weight: 700;">{{ $quotation->quotation_no }}</div>
                <div style="margin-top: 4px;">
                    <span class="badge {{ $quotation->status === 'converted' ? 'badge-success' : 'badge-primary' }}">
                        {{ strtoupper($quotation->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="table-container" style="margin-bottom: 1.5rem;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Product Description</th>
                        <th style="text-align: center; width: 120px;">Quantity</th>
                        <th style="text-align: right; width: 140px;">Unit Rate (৳)</th>
                        <th style="text-align: right; width: 140px;">Amount (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $idx => $it)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $it->product->name }}</strong>
                            <div style="font-size: 11px; color: var(--muted-foreground); font-family: monospace;">{{ $it->product->barcode }}</div>
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $it->quantity }} {{ $it->unit?->short_code }}</td>
                        <td style="text-align: right;">৳{{ number_format($it->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: 700;">৳{{ number_format($it->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 280px; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span>Subtotal:</span>
                    <strong>৳{{ number_format($quotation->subtotal, 2) }}</strong>
                </div>
                @if($quotation->discount > 0)
                <div style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--danger);">
                    <span>Discount:</span>
                    <strong>-৳{{ number_format($quotation->discount, 2) }}</strong>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-top: 2px solid var(--border); font-size: 1.15rem; font-weight: 800;">
                    <span>Estimated Total:</span>
                    <strong style="color: var(--primary);">৳{{ number_format($quotation->grand_total, 2) }}</strong>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection