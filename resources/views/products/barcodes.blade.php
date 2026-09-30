<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>বারকোড স্টিকার প্রিন্ট - {{ $product->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 0; background: #fff; }
        .no-print { background: #0f172a; color: white; padding: 12px; display: flex; justify-content: space-between; align-items: center; }
        .barcode-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6mm; margin-top: 5mm; }
        .barcode-sticker { border: 1px dashed #cbd5e1; padding: 6px; border-radius: 4px; text-align: center; page-break-inside: avoid; }
        .store-title { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .prod-title { font-size: 11px; font-weight: 600; color: #0f172a; margin: 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .price-tag { font-size: 13px; font-weight: 800; color: #000; margin-top: 2px; }
        .barcode-svg { max-width: 100%; margin: 2px 0; }
        .code-text { font-family: monospace; font-size: 9px; letter-spacing: 1px; color: #334155; }
        @media print { .no-print { display: none !important; } .barcode-sticker { border: 1px solid #e2e8f0; } }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong>{{ $product->name }}</strong> (Barcode: {{ $product->barcode }})
        </div>
        <div>
            <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
                🖨️ Print {{ $quantity }} Stickers
            </button>
        </div>
    </div>

    <div class="barcode-grid">
        @for($i = 0; $i < $quantity; $i++)
            <div class="barcode-sticker">
                <div class="store-title">{{ \App\Models\ShopSetting::get('shop_name', 'Madina General Store') }}</div>
                <div class="prod-title">{{ $product->name }}</div>
                <div class="barcode-svg">
                    {!! \App\Helpers\BarcodeHelper::generateSvg($product->barcode, 36) !!}
                </div>
                <div class="code-text">{{ $product->barcode }}</div>
                <div class="price-tag">৳{{ number_format($product->selling_price, 2) }}</div>
            </div>
        @endfor
    </div>
</body>
</html>