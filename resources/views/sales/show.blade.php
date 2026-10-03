<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>রসিদ #{{ $sale->invoice_no }}</title>
    <style>
        @page { size: {{ \App\Models\ShopSetting::get('thermal_width', '80mm') }} auto; margin: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Hind Siliguri", sans-serif; margin: 0; padding: 12px; width: {{ \App\Models\ShopSetting::get('thermal_width', '80mm') }}; background: #fff; color: #000; font-size: 12px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .table-slip { width: 100%; border-collapse: collapse; font-size: 11px; }
        .table-slip th { border-bottom: 1px solid #000; padding: 4px 0; text-align: left; }
        .table-slip td { padding: 4px 0; }
        .no-print { background: #0f172a; color: white; padding: 10px; text-align: center; margin-bottom: 10px; border-radius: 4px; }
        @media print { .no-print { display: none !important; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 6px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Print Receipt
        </button>
    </div>

    <!-- Store Header -->
    <div class="center">
        <div style="font-size: 10px;">{{ \App\Models\ShopSetting::get('invoice_header', 'বিসমিল্লাহির রাহমানির রাহিম') }}</div>
        <div class="bold" style="font-size: 15px; margin: 2px 0;">{{ \App\Models\ShopSetting::get('shop_name', 'মদিনা জেনারেল স্টোর') }}</div>
        <div style="font-size: 10px;">{{ $sale->branch->name }} • {{ $sale->branch->phone ?? '' }}</div>
        @if(\App\Models\ShopSetting::get('bin_vat_number'))
            <div style="font-size: 9px; font-family: monospace;">BIN: {{ \App\Models\ShopSetting::get('bin_vat_number') }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Meta Details -->
    <div style="font-size: 10px;">
        <div><strong>Invoice:</strong> {{ $sale->invoice_no }}</div>
        <div><strong>Date:</strong> {{ $sale->sale_date->format('d/m/Y') }} {{ $sale->created_at->format('h:i A') }}</div>
        <div><strong>Customer:</strong> {{ $sale->customer?->name ?? 'Cash Customer' }}</div>
        <div><strong>Cashier:</strong> {{ $sale->cashier?->name }}</div>
    </div>

    <div class="divider"></div>

    <!-- Items Table -->
    <table class="table-slip">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $it)
            <tr>
                <td>{{ $it->product->name }}</td>
                <td style="text-align: center;">{{ $it->quantity }}</td>
                <td class="right">{{ number_format($it->unit_price, 2) }}</td>
                <td class="right">{{ number_format($it->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Totals -->
    <div style="font-size: 11px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>৳{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        @if($sale->discount > 0)
        <div style="display: flex; justify-content: space-between;">
            <span>Discount:</span>
            <span>-৳{{ number_format($sale->discount, 2) }}</span>
        </div>
        @endif
        @if($sale->vat_amount > 0)
        <div style="display: flex; justify-content: space-between;">
            <span>VAT ({{ $sale->vat_percent }}%):</span>
            <span>+৳{{ number_format($sale->vat_amount, 2) }}</span>
        </div>
        @endif
        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: bold; margin-top: 2px;">
            <span>Net Payable:</span>
            <span>৳{{ number_format($sale->grand_total, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Paid ({{ strtoupper($sale->payment_method) }}):</span>
            <span>৳{{ number_format($sale->paid_amount, 2) }}</span>
        </div>
        @if($sale->change_amount > 0)
        <div style="display: flex; justify-content: space-between;">
            <span>Change Return (ফেরত):</span>
            <span>৳{{ number_format($sale->change_amount, 2) }}</span>
        </div>
        @endif
        @if($sale->due_amount > 0)
        <div style="display: flex; justify-content: space-between; font-weight: bold; color: red;">
            <span>Current Due (বাকি):</span>
            <span>৳{{ number_format($sale->due_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Footer Policy -->
    <div class="center" style="font-size: 9px; margin-top: 6px;">
        {{ \App\Models\ShopSetting::get('invoice_footer', 'ধন্যবাদ আবার আসবেন!') }}
    </div>
</body>
</html>