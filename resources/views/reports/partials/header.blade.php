<div class="report-header-banner" style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <div style="text-align: left;">
            <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: #000;">{{ $shop['name'] }}</h1>
            <div style="font-size: 0.85rem; font-weight: 600; color: #333;">{{ $shop['name_en'] }}</div>
            <div style="font-size: 0.75rem; color: #555;">{{ $shop['address'] }} | Phone: {{ $shop['phone'] }}</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; color: #555;">Print Date & Time:</div>
            <div style="font-size: 0.85rem; font-weight: 700; font-family: monospace; color: #000;">{{ $shop['print_time'] }}</div>
        </div>
    </div>
    <div style="background: #f1f5f9; padding: 6px 12px; border-radius: 4px; display: inline-block; border: 1px solid #cbd5e1;">
        <strong style="font-size: 1rem; color: #0f172a; text-transform: uppercase;">{{ $reportTitle }}</strong>
        @if(isset($fromDate) && isset($toDate))
            <span style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-left: 8px;">
                (Period: {{ date('d M, Y', strtotime($fromDate)) }} to {{ date('d M, Y', strtotime($toDate)) }})
            </span>
        @endif
    </div>
</div>