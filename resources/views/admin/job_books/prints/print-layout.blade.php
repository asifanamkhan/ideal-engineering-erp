<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ideal Engineering Works - Print Documents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <style>
        * { margin:0 0.3px; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; background:#f0f0f0; }

        /* Print Preview Toolbar */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 12px 20px;
            z-index: 1000;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-bottom: 1px solid #ddd;
        }

        .print-toolbar button {
            padding: 8px 20px;
            font-size: 15px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-print:hover {
            background: #218838;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        /* Add margin to body to account for fixed toolbar */
        body {
            padding-top: 60px !important;
            font-family: "Roboto Condensed", sans-serif;
        }

        .screen-wrap {
            max-width: 800px;
            margin: 0 auto 20px auto;
            background:#fff;
            padding:20px;
            box-shadow:0 0 12px rgba(0,0,0,0.15);
            overflow-x: auto;
        }

        /* Document separator */
        .document-separator {
            margin: 40px 0 20px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #e74a3b;
            border-radius: 4px;
        }

        .document-separator h3 {
            color: #e74a3b;
            font-size: 18px;
            margin: 0;
        }

        .document-separator p {
            color: #666;
            font-size: 13px;
            margin: 5px 0 0 0;
        }

        /* OUTER PAGE TABLE - thead of this repeats on every print page */
        table.page-table {
            width:100%;
            border-collapse:collapse;
            table-layout: fixed;
            margin-bottom: 30px;
        }
        table.page-table > thead { display:table-header-group; }
        table.page-table > tbody { display:table-row-group; }
        table.page-table > tfoot { display:table-footer-group; }

        /* COMPANY HEADER */
        .bill-header { text-align:center; border-bottom:2px solid #000; padding-bottom:8px; margin-bottom:6px; }
        .company-name { font-size:25px; font-weight:900; letter-spacing:1.2px; text-transform:uppercase; }
        .company-sub { font-size:13px; line-height:1.5; margin-top:3px; }
        .address-line { font-size:15px; font-weight:bold; margin-top:4px; }
        .contact-line { font-size:13px; margin-top:2px; }

        /* TO / DATE */
        .to-section { display:flex; justify-content:space-between; align-items:flex-start; margin:8px 0 6px; font-size:13.5px; }

        /* QUOTATION LABEL */
        .quotation-label { text-align:center; font-weight:bold; font-size:15px; background:#e8e8e8; border:0.5px solid #000; padding:3px; margin-bottom: 10px; }

        /* FIXED ITEMS TABLE */
        table.items {
            width:100%;
            border-collapse:collapse;
            font-size:13.5px;
            table-layout: fixed;
        }

        table.items th,
        table.items td {
            border:0.5px solid #000 !important;
            padding:4px 6px;
        }

        table.items th {
            background:#f0f0f0;
            font-weight:bold;
            text-align:center;
        }

        /* TOTALS - Fixed */
        .total-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .total-section td {
            padding: 4px 6px;
            font-weight: bold;
            font-size: 13.5px;
        }

        .total-section .lbl {
            text-align: right;
            border: none;
            width: 85%;
        }

        .total-section .val {
            text-align: right;
            border: 0.5px solid #000 !important;
            width: 15%;
            padding: 6px 8px;
        }

        /* FOOTER */
        .words-section {
            margin-top: 15px;
            font-size: 13.5px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }

        .bill-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 70px;
            font-size: 13.5px;
            font-weight: bold;
        }

        .sig-right {
            text-align: center;
        }

        /* PRINT STYLES */
        /* UPDATE these print styles in your main layout */
@media print {
    body {
        background:#fff;
        padding-top: 0 !important;
    }
    .print-toolbar {
        display: none !important;
    }
    .screen-wrap {
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: 100%;
        overflow-x: visible;
    }
    .document-separator {
        display: none;
    }

    /* Remove extra page breaks */
    .page-table {
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
    }

    /* Only break between different documents */
    .page-break-document {
        page-break-after: always;
    }

    .no-page-break {
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
    }

    /* Ensure borders show in print */
        table.items th,
        table.items td,
        .total-section .val {
            border: 0.5px solid #000 !important;
        }
}

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

<!-- Print Toolbar -->
<div class="print-toolbar">
    <button class="btn-print" onclick="window.print();">
        🖨️ Print / Save as PDF
    </button>
    <button class="btn-back" onclick="window.close();">
        ⬅️ Back
    </button>
</div>

<div class="screen-wrap">
    @foreach($documents as $index => $document)
        <!-- Only add page break before document except the first one -->
        @if($index > 0)
            <div class="page-break-document"></div>
        @endif

        <!-- Document Separator for screen view only -->
        <div class="document-separator">
            <h3>
                @if($document == 'invoice')
                    📄 INVOICE
                @elseif($document == 'quotation')
                    📋 QUOTATION
                @elseif($document == 'parts')
                    🔧 PARTS LIST
                @endif
            </h3>
            <p>Document {{ $index + 1 }} of {{ count($documents) }}</p>
        </div>

        @if($document == 'invoice')
            @include('admin.job_books.prints.invoice', ['job' => $jobData])
        @elseif($document == 'quotation')
            @include('admin.job_books.prints.quotation', ['job' => $jobData])
        @elseif($document == 'parts')
            @include('admin.job_books.prints.parts', ['job' => $jobData])
        @endif
    @endforeach
</div>

<script>
    // Optional: Show document count in console
    console.log('Total documents to print: {{ count($documents) }}');
</script>

</body>
</html>
