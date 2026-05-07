<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Challan - {{ $job->job_id ?? 'N/A' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <style>
        * { margin:0 0.3px; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; background:#f0f0f0; }

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
        }

        .btn-print { background: #28a745; color: white; }
        .btn-back { background: #6c757d; color: white; }

        body { padding-top: 60px !important; font-family: "Roboto Condensed", sans-serif; }

        .screen-wrap {
            max-width: 800px;
            margin: 0 auto 20px auto;
            background:#fff;
            padding:20px;
            box-shadow:0 0 12px rgba(0,0,0,0.15);
            overflow-x: auto;
        }

        .bill-header { text-align:center; border-bottom:2px solid #000; padding-bottom:8px; margin-bottom:6px; }
        .company-name { font-size:25px; font-weight:900; text-transform:uppercase; }
        .company-sub { font-size:13px; line-height:1.5; margin-top:3px; }
        .address-line { font-size:15px; font-weight:bold; margin-top:4px; }
        .contact-line { font-size:13px; margin-top:2px; }

        .to-section { display:flex; justify-content:space-between; align-items:flex-start; margin:8px 0 6px; font-size:13.5px; }
        .quotation-label { text-align:center; font-weight:bold; font-size:15px; background:#e8e8e8; border:0.5px solid #000; padding:3px; margin-bottom: 10px; }

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

        /* Description Table */
        table.description-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            margin-top: 10px;
            page-break-inside: auto;
        }

        table.description-table th,
        table.description-table td {
            border: 0.5px solid #000 !important;
            padding: 6px 8px;
            vertical-align: top;
        }

        table.description-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        table.description-table td {
            text-align: left;
        }

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

        /* Page break for descriptions */
        .description-table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        @media print {
            body { background:#fff; padding-top: 0 !important; }
            .print-toolbar { display: none !important; }
            .screen-wrap { box-shadow: none; margin: 0; padding: 0; max-width: 100%; overflow-x: visible; }
            .no-page-break { page-break-after: avoid !important; page-break-inside: avoid !important; }
            table.items th, table.items td, .total-section .val, .description-table th, .description-table td { border: 0.5px solid #000 !important; }

            /* Allow page breaks inside table for long content */
            .description-table {
                page-break-inside: auto;
            }
            .description-table tbody {
                page-break-inside: auto;
            }
            .description-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

<div class="print-toolbar">
    <button class="btn-print" onclick="window.print();">🖨️ Print / Save as PDF</button>
    <button class="btn-back" onclick="window.close();">⬅️ Back</button>
</div>

<div class="screen-wrap">
    <div class="invoice-wrapper no-page-break">
        @php
            $invoice_settings = DB::table('invoice_settings')->first();
            $general_settings = DB::table('generel_settings')->first();
        @endphp

        <!-- Company Header -->
        @if($invoice_settings && $invoice_settings->header_text)
        <div class="bill-header">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                @if($invoice_settings && $invoice_settings->logo)
                    <div style="width: 120px;">
                        <img src="{{ asset($invoice_settings->logo) }}" alt="Logo" style="max-width: 110px; max-height: 80px; object-fit: contain;">
                    </div>
                @endif
                <div style="flex: 1; text-align: center;">
                    {!! $invoice_settings->header_text !!}
                </div>
                @if($invoice_settings && $invoice_settings->logo)
                    <div style="width: 120px;"></div>
                @endif
            </div>
        </div>
        @else
        <div class="bill-header">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                @if($invoice_settings && $invoice_settings->logo)
                    <div style="width: 120px;">
                        <img src="{{ asset($invoice_settings->logo) }}" alt="Logo" style="max-width: 110px; max-height: 80px; object-fit: contain;">
                    </div>
                @endif
                <div style="flex: 1; text-align: center;">
                    <div class="company-name">{{ $invoice_settings->company_name ?? 'Ideal Engineering Works' }}</div>
                    <div class="company-sub">{{ $invoice_settings->company_description ?? '...' }}</div>
                    <div class="address-line">{{ $invoice_settings->address ?? 'Milgate (Sonali Road), Tongi, Gazipur.' }}</div>
                    <div class="contact-line">Phone: {{ $invoice_settings->phone ?? '02-9816620' }}, Cell: {{ $invoice_settings->mobile ?? '01725-126517' }}</div>
                    <div class="contact-line">email: {{ $invoice_settings->email ?? 'idealengineering.manager@gmail.com' }}</div>
                </div>
                @if($invoice_settings && $invoice_settings->logo)
                    <div style="width: 120px;"></div>
                @endif
            </div>
        </div>
        @endif

        @if($invoice_settings && $invoice_settings->footer_text)
        <div class="invoice-footer" style="margin-top: 20px; text-align: center; font-size: 11px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
            {!! $invoice_settings->footer_text !!}
        </div>
        @endif
        </div>

        <div class="to-section">
            <div>
                <div>To,</div>
                <div style="margin-left:16px;font-weight:bold;">{{ $job->customer->name ?? 'N/A' }}</div>
                <div style="margin-left:16px;">{{ $job->customer->address ?? 'N/A' }}</div>
                @if($job->customer->phone ?? false)
                <div style="margin-left:16px;">Phone: {{ $job->customer->phone }}</div>
                @endif
            </div>
            <div>
                <span style="font-weight:bold;">Engine:</span> {{ $job->engine ?? 'N/A' }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-weight:bold;">Job ID:</span> {{ $job->job_id ?? 'N/A' }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-weight:bold;">Date:</span> {{ date('d/m/Y', strtotime($job->invoice->invoice_date ?? $job->date ?? now())) }}
            </div>
        </div>

        <div class="quotation-label">DELIVERY CHALLAN</div>

        <!-- Check which format to display -->
        @if(in_array('with_service', $formats) && isset($job->invoice->items) && count($job->invoice->items) > 0)
            <!-- With Service List - Show Table -->
            <table class="items">
                <thead>
                    <tr>
                        <th style="width:10%">Sl No</th>
                        <th style="width:40%">Service Name</th>
                        <th style="width:10%">Unit</th>
                        <th style="width:10%">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0; @endphp
                    @foreach($job->invoice->items as $key => $item)
                        @php
                            $itemTotal = ($item->quantity ?? 0) * ($item->price ?? 0);
                            $subtotal += $itemTotal;
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td style="text-align:left;">{{ $item->service_name ?? 'Service Item' }}</td>
                            <td style="text-align:center;">{{ $item->unit_name ?? '-' }}</td>
                            <td style="text-align:center;">{{ $item->quantity ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif(in_array('without_service', $formats))
            <!-- Without Service List - Show Descriptions in Table with 2 Columns -->
            <table class="description-table">
                <thead>
                    <tr>
                        <th style="width:10%">SL No.</th>
                        <th style="width:90%">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($job->descriptions as $index => $desc)
                        <tr>
                            <td style="text-align:center; vertical-align:top;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td style="text-align:left;">{{ $desc->description ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;">No descriptions available for this job.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="bill-footer" style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div style="text-align: center; width: 40%;">
                <div style="border-top: 1px solid #000; padding-top: 5px;">Receiver Signature</div>
            </div>
            <div style="text-align: center; width: 40%;">
                @if($invoice_settings && $invoice_settings->author_signature)
                    <div>
                        <img src="{{ asset($invoice_settings->author_signature) }}" alt="Signature" style="max-height: 40px;">
                    </div>
                @endif
                <div style="border-top: 1px solid #000; padding-top: 5px;">Authorized Signature</div>
            </div>
        </div>
        @if($invoice_settings && $invoice_settings->footer_text)
            <div class="invoice-footer" style="margin-top: 25px; text-align: center; font-size: 11px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
                {!! $invoice_settings->footer_text !!}
            </div>
        @endif
    </div>
</div>

</body>
</html>
