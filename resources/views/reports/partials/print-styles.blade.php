<style>
@media print {
    /* 1. Suppress browser's default URL, title, and timestamp margins */
    @page {
        size: A4 portrait;
        margin: 0mm !important;
    }

    /* 2. Hide everything except printable report */
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body * { 
        visibility: hidden !important; 
    }

    .page-header, .filter-card, .btn, .breadcrumb, nav, aside, header, footer:not(.print-only-footer) { 
        display: none !important; 
    }

    #printable-report, #printable-report * {
        visibility: visible !important;
    }

    #printable-report {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 12mm 15mm !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-family: 'Hind Siliguri', 'Inter', sans-serif !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-top: 10px !important;
        page-break-inside: auto !important;
    }

    tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }

    table th, table td {
        border: 1px solid #000000 !important;
        padding: 5px 8px !important;
        color: #000000 !important;
        font-size: 11px !important;
    }

    table thead th {
        background-color: #f1f5f9 !important;
        color: #000000 !important;
        font-weight: 700 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .no-print {
        display: none !important;
    }
}
</style>