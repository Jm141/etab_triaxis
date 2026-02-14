<style>
/* Screen styles - hide print elements */
.print-table-container {
    display: none;
}

/* Tabulation header visible on screen */
.tabulation-header {
    text-align: center;
    margin-bottom: 20px;
    display: block;
}

.tabulation-header .event-name {
    font-size: 18pt;
    font-weight: bold;
    margin: 10px 0;
    text-transform: uppercase;
}

.tabulation-header .event-details {
    font-size: 11pt;
    margin: 5px 0;
}

.tabulation-header .tabulation-title {
    font-size: 14pt;
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.tabulation-header .divider {
    border-bottom: 2px solid #000;
    margin: 10px 0;
}

/* Common Print Styles for All Reports - Landscape Orientation */
@media print {
    @page {
        size: landscape;
        margin: 1.5cm;
    }
    
    /* Hide non-printable elements */
    .btn, .card-header .btn, .d-flex .btn,
    .navbar, .sidebar, .breadcrumb,
    .alert:not(.print-alert), .no-print {
        display: none !important;
    }
    
    /* Tabulation Sheet Header Style */
    .tabulation-header {
        text-align: center;
        margin-bottom: 20px;
        page-break-after: avoid;
        display: block !important;
    }
    
    /* Hide screen header, show print header */
    .tabulation-header.no-print {
        display: none !important;
    }
    
    .tabulation-table {
        display: table !important;
    }
    
    /* Print table container - hidden on screen, shown when printing */
    .print-table-container {
        display: none !important;
    }
    
    .tabulation-header .org-info {
        font-size: 11pt;
        margin-bottom: 5px;
    }
    
    .tabulation-header .event-name {
        font-size: 16pt;
        font-weight: bold;
        margin: 10px 0;
        text-transform: uppercase;
    }
    
    .tabulation-header .event-details {
        font-size: 10pt;
        margin: 5px 0;
    }
    
    .tabulation-header .tabulation-title {
        font-size: 14pt;
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 10px;
        text-transform: uppercase;
    }
    
    .tabulation-header .divider {
        border-bottom: 2px solid #000;
        margin: 10px 0;
    }
    
    /* Report Header (for screen view) */
    .report-header {
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 15px;
        page-break-after: avoid;
    }
    
    .report-header h2 {
        margin: 0;
        font-size: 18pt;
        font-weight: bold;
    }
    
    .report-header .report-info {
        margin-top: 5px;
        font-size: 10pt;
    }
    
    .report-header .report-info strong {
        font-weight: bold;
    }
    
    /* Tables - Tabulation Sheet Style */
    .tabulation-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        margin-top: 15px;
    }
    
    .tabulation-table th, .tabulation-table td {
        border: 1px solid #000;
        padding: 8px 5px;
        text-align: center;
    }
    
    .tabulation-table th {
        background-color: #fff !important;
        font-weight: bold;
        font-size: 10pt;
    }
    
    .tabulation-table td {
        font-size: 10pt;
    }
    
    .tabulation-table .contestant-no {
        width: 15%;
    }
    
    .tabulation-table .judge-col {
        width: 12%;
    }
    
    .tabulation-table .total-col {
        width: 12%;
        font-weight: bold;
    }
    
    .tabulation-table .rank-col {
        width: 8%;
        font-weight: bold;
    }
    
    /* Highlight top 3 rows (rank 1, 2, 3 or ties) in tabulation print view */
    .tabulation-table tr.table-warning,
    .tabulation-table tr.top-three-row {
        background-color: #fff3cd !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .tabulation-table tr.table-warning td,
    .tabulation-table tr.top-three-row td {
        background-color: #fff3cd !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Regular Tables */
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    
    .table th, .table td {
        border: 1px solid #000;
        padding: 5px;
        text-align: left;
    }
    
    .table th {
        background-color: #f0f0f0 !important;
        font-weight: bold;
        text-align: center;
    }
    
    .table td {
        text-align: center;
    }
    
    /* Page breaks */
    .page-break {
        page-break-before: always;
    }
    
    .page-break-inside-avoid {
        page-break-inside: avoid;
    }
    
    /* Cards */
    .card {
        border: none !important;
        box-shadow: none !important;
        page-break-inside: avoid;
        margin-bottom: 10px;
    }
    
    .card-header {
        display: none !important;
    }
    
    .card-body {
        padding: 0;
    }
    
    /* Ensure tables fit on page */
    .table-responsive {
        overflow: visible;
    }
    
    /* Print-specific spacing */
    body {
        margin: 0;
        padding: 0;
        font-family: 'Times New Roman', serif;
    }
    
    .mb-4, .mb-3 {
        margin-bottom: 10px !important;
    }
    
    /* Show print table container when printing */
    .print-table-container {
        display: block !important;
    }
    
    /* Hide screen table when printing */
    .card.no-print {
        display: none !important;
    }
}
</style>
