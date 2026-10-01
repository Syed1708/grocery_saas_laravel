@extends('tyro-dashboard::layouts.admin')

@section('title', 'Purchase Invoice #' . $purchase->chalan_no)

@section('breadcrumb')
    <a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('purchases.index') }}">Purchases</a>
    <span class="breadcrumb-separator">/</span>
    <span>{{ $purchase->chalan_no }}</span>
@endsection

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Purchase Chalan #{{ $purchase->chalan_no }}</h1>
                <p class="page-description">Procured from {{ $purchase->supplier->name }} on
                    {{ $purchase->purchase_date->format('M d, Y') }}</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('purchases.edit', ['purchase' => $purchase->id]) }}" class="btn btn-secondary">
                    ✏️ Edit Chalan
                </a>
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print Voucher</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">← Back to Purchases</a>
            </div>
        </div>
    </div>

    <div class="card" style="max-width: 850px; margin: 0 auto; border-radius: 8px;">
        <div class="card-body" style="padding: 1.5rem;">

            <!-- Voucher Top -->
            <div
                style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--border); padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--foreground);">
                        {{ \App\Models\ShopSetting::get('shop_name', 'M/S Madina General Store') }}
                    </h2>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 4px;">
                        Branch: {{ $purchase->branch->name }}
                    </div>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground);">
                        Phone: {{ $purchase->branch->phone ?? 'N/A' }}
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1rem; font-weight: 800; color: var(--primary);">PURCHASE VOUCHER</div>
                    <div style="font-family: monospace; font-size: 0.875rem; font-weight: 700; margin-top: 4px;">
                        {{ $purchase->chalan_no }}
                    </div>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground);">
                        Date: {{ $purchase->purchase_date->format('d M, Y') }}
                    </div>
                </div>
            </div>

            <!-- Supplier Info & Payment Status -->
            <div
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; background: var(--muted, rgba(148, 163, 184, 0.08)); padding: 1rem; border-radius: 8px;">
                <div>
                    <div
                        style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--muted-foreground);">
                        Supplier Information</div>
                    <strong
                        style="font-size: 1rem; color: var(--foreground); display: block; margin-top: 2px;">{{ $purchase->supplier->name }}</strong>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground);">
                        {{ $purchase->supplier->company_name }}</div>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground); font-family: monospace;">Phone:
                        {{ $purchase->supplier->phone }}</div>
                </div>
                <div style="text-align: right;">
                    <div
                        style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--muted-foreground);">
                        Payment Status</div>
                    <div style="margin-top: 4px;">
                        <span
                            class="badge {{ $purchase->payment_status === 'paid' ? 'badge-success' : ($purchase->payment_status === 'partial' ? 'badge-warning' : 'badge-danger') }}"
                            style="font-size: 0.875rem;">
                            {{ strtoupper($purchase->payment_status) }}
                        </span>
                    </div>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 4px;">Payment Method:
                        {{ strtoupper($purchase->payment_method) }}</div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-container" style="margin-bottom: 1.25rem;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Product Description</th>
                            <th style="text-align: center; width: 120px;">Quantity</th>
                            <th style="text-align: right; width: 130px;">Unit Rate (৳)</th>
                            <th style="text-align: right; width: 130px;">Commission (৳)</th>
                            <th style="text-align: right; width: 140px;">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->items as $idx => $it)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $it->product->name }}</strong>
                                    <div
                                        style="font-size: 0.75rem; color: var(--muted-foreground); font-family: monospace;">
                                        Barcode: {{ $it->product->barcode }}</div>
                                </td>
                                <td style="text-align: center; font-weight: 600;">{{ $it->quantity }}
                                    {{ $it->unit?->short_code }}</td>
                                <td style="text-align: right; font-mono;">৳{{ number_format($it->unit_cost, 2) }}</td>
                                <td style="text-align: right; color: var(--danger, #ef4444);">
                                    -৳{{ number_format($it->commission ?? 0, 2) }}</td>
                                <td style="text-align: right; font-weight: 700;">৳{{ number_format($it->line_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary -->
            <div style="display: flex; justify-content: flex-end;">
                <div style="width: 320px; font-size: 0.875rem;">
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span>Total Quantity:</span>
                        <strong>{{ $purchase->total_quantity ?? $purchase->items->sum('quantity') }} items</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span>Subtotal:</span>
                        <strong>৳{{ number_format($purchase->subtotal, 2) }}</strong>
                    </div>
                    @if ($purchase->discount > 0)
                        <div
                            style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--danger, #ef4444);">
                            <span>Discount:</span>
                            <strong>-৳{{ number_format($purchase->discount, 2) }}</strong>
                        </div>
                    @endif
                    @if ($purchase->transport_cost > 0)
                        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                            <span>Labor / Freight:</span>
                            <strong>+৳{{ number_format($purchase->transport_cost, 2) }}</strong>
                        </div>
                    @endif
                    @if ($purchase->vat_amount > 0)
                        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                            <span>VAT ({{ $purchase->vat_percent }}%):</span>
                            <strong>+৳{{ number_format($purchase->vat_amount, 2) }}</strong>
                        </div>
                    @endif

                    <div
                        style="display: flex; justify-content: space-between; padding: 6px 0; border-top: 1px solid var(--border); font-size: 1rem; font-weight: 700;">
                        <span>Invoice Total:</span>
                        <strong style="color: var(--primary);">৳{{ number_format($purchase->grand_total, 2) }}</strong>
                    </div>

                    @if ($purchase->previous_due > 0)
                        <div
                            style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--danger, #ef4444); font-size: 0.8125rem;">
                            <span>Previous Due (পূর্বের বকেয়া):</span>
                            <strong>+৳{{ number_format($purchase->previous_due, 2) }}</strong>
                        </div>
                        <div
                            style="display: flex; justify-content: space-between; padding: 6px 0; border-top: 1px dashed var(--border); font-size: 1rem; font-weight: 800;">
                            <span>Net Total Payable:</span>
                            <strong>৳{{ number_format($purchase->grand_total + $purchase->previous_due, 2) }}</strong>
                        </div>
                    @endif

                    <div
                        style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--success, #10b981); font-weight: 700;">
                        <span>Paid Amount:</span>
                        <strong>৳{{ number_format($purchase->paid_amount, 2) }}</strong>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 6px 0; color: var(--danger, #ef4444); font-weight: 800; font-size: 1.05rem; border-top: 2px solid var(--border);">
                        <span>Remaining Due (বাকি):</span>
                        <strong>৳{{ number_format($purchase->due_amount, 2) }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
