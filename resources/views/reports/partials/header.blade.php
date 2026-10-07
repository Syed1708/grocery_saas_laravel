<div class="report-header-banner" style="text-align: center; border-bottom: 2px solid var(--border, #cbd5e1); padding-bottom: 12px; margin-bottom: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <div style="text-align: left;">
            <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--foreground, #fff);">{{ $shop['name'] }}</h1>
            <div style="font-size: 0.85rem; font-weight: 600; color: var(--muted-foreground, #94a3b8);">{{ $shop['name_en'] }}</div>
            <div style="font-size: 0.75rem; color: var(--muted-foreground, #94a3b8);">{{ $shop['address'] }} | Phone: {{ $shop['phone'] }}</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; color: var(--muted-foreground, #94a3b8);">Print Date & Time:</div>
            <div style="font-size: 0.85rem; font-weight: 700; font-family: monospace; color: var(--foreground, #fff);">{{ $shop['print_time'] }}</div>
        </div>
    </div>
    <div style="background: var(--muted, rgba(148, 163, 184, 0.1)); padding: 6px 14px; border-radius: 6px; display: inline-block; border: 1px solid var(--border, #334155);">
        <strong style="font-size: 0.95rem; color: var(--foreground, #fff); text-transform: uppercase;">{{ $reportTitle }}</strong>
        @if(isset($fromDate) && isset($toDate))
            <span style="font-size: 0.85rem; font-weight: 600; color: var(--muted-foreground, #94a3b8); margin-left: 8px;">
                (Period: {{ date('d M, Y', strtotime($fromDate)) }} to {{ date('d M, Y', strtotime($toDate)) }})
            </span>
        @endif
    </div>
</div>