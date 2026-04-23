<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto Condensed', sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

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
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-print { background: #28a745; color: white; }
        .btn-back { background: #6c757d; color: white; }

        body { padding-top: 60px; }

        .receipt-wrapper {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .bill-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 12px;
            color: #555;
            margin-top: 5px;
        }

        .address-line {
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
        }

        .contact-line {
            font-size: 11px;
            margin-top: 3px;
        }

        .receipt-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            padding: 6px;
            background: #f0f0f0;
            letter-spacing: 2px;
        }

        .info-section {
            margin-bottom: 20px;
            font-size: 13px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px dotted #ddd;
        }

        .info-label {
            font-weight: bold;
            width: 35%;
        }

        .info-value {
            width: 65%;
        }

        .amount-section {
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fc;
            border-radius: 5px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .grand-total {
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 16px;
        }

        .grand-total .amount-label,
        .grand-total .amount-value {
            font-weight: bold;
        }

        .payment-details {
            margin: 15px 0;
            padding: 12px;
            background: #f0f0f0;
            border-radius: 5px;
        }

        .receipt-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 10px;
        }

        .sig-line {
            text-align: center;
            width: 40%;
        }

        .sig-line .line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
        }

        @media print {
            body { background: #fff; padding-top: 0; }
            .print-toolbar { display: none; }
            .receipt-wrapper { box-shadow: none; padding: 15px; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="print-toolbar">
    <button class="btn-print" onclick="window.print();">🖨️ Print / Save as PDF</button>
    <button class="btn-back" onclick="window.close();">⬅️ Back</button>
</div>

<div class="receipt-wrapper">
    @php
        $general_settings = DB::table('generel_settings')->first();
    @endphp

    <div class="bill-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            @if(isset($general_settings->logo) && $general_settings->logo)
                <div style="width: 100px;">
                    <img src="{{ asset($general_settings->logo) }}" alt="Logo" style="max-width: 90px; max-height: 70px; object-fit: contain;">
                </div>
            @endif
            <div style="flex: 1; text-align: center;">
                <div class="company-name">{{ $general_settings->company_name ?? 'Ideal Engineering Works' }}</div>
                <div class="company-sub">
                    {{ $general_settings->company_description ?? 'All kinds Of Marine, Petrol, Diesel, Gas Generator, MWM, Caterpillar, Waukesha, Jenbacher, Wartsila, Perkins, Cummins, Mitsubishi, Hino, Ashok Leyland, TATA, Excavator Engine & Mill Factory\'s Any Spare Parts Repairing By Expert Hands' }}
                </div>
                <div class="address-line">{{ $general_settings->address ?? 'Milgate (Sonali Road), Tongi, Gazipur.' }}</div>
                <div class="contact-line">Phone: {{ $general_settings->phone ?? '02-9816620' }}, Cell: {{ $general_settings->mobile ?? '01725-126517, 01855956767, 01716657171, 01981022952' }}</div>
                <div class="contact-line">email: {{ $general_settings->email ?? 'azizur.rahman6767@gmail.com, idealengineering.manager@gmail.com' }}</div>
            </div>
            @if(isset($general_settings->logo) && $general_settings->logo)
                <div style="width: 100px;"></div>
            @endif
        </div>
    </div>

    <div class="receipt-title">💰 PAYMENT RECEIPT</div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Receipt No:</span>
            <span class="info-value">RCPT-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Transaction ID:</span>
            <span class="info-value">{{ $payment->tran_id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ date('d-m-Y h:i A', strtotime($payment->payment_date)) }}</span>
        </div>
        @if($relatedData && isset($relatedData->customer_name))
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $relatedData->customer_name }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Type:</span>
            <span class="info-value">{{ ucfirst($payment->type) }}</span>
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-row">
            <span class="amount-label">Payment Amount:</span>
            <span class="amount-value">৳ {{ number_format($payment->amount, 2) }}</span>
        </div>
        <div class="amount-row grand-total">
            <span class="amount-label">Total Paid:</span>
            <span class="amount-value">৳ {{ number_format($payment->amount, 2) }}</span>
        </div>
    </div>

    <div class="payment-details">
        <div class="amount-row">
            <span class="amount-label">Payment Mode:</span>
            <span class="amount-value">{{ $payment->payment_mode ?? 'N/A' }}</span>
        </div>
        @if($payment->chq_no)
        <div class="amount-row">
            <span class="amount-label">Cheque No:</span>
            <span class="amount-value">{{ $payment->chq_no }}</span>
        </div>
        @endif
        @if($payment->chq_date)
        <div class="amount-row">
            <span class="amount-label">Cheque Date:</span>
            <span class="amount-value">{{ date('d-m-Y', strtotime($payment->chq_date)) }}</span>
        </div>
        @endif
        @if($payment->card_no)
        <div class="amount-row">
            <span class="amount-label">Card No:</span>
            <span class="amount-value">{{ $payment->card_no }}</span>
        </div>
        @endif
        @if($payment->online_trx_id)
        <div class="amount-row">
            <span class="amount-label">Transaction ID:</span>
            <span class="amount-value">{{ $payment->online_trx_id }}</span>
        </div>
        @endif
        @if($payment->mfs_name)
        <div class="amount-row">
            <span class="amount-label">MFS Name:</span>
            <span class="amount-value">{{ $payment->mfs_name }}</span>
        </div>
        @endif
        @if($payment->bank_code)
        <div class="amount-row">
            <span class="amount-label">Bank Code:</span>
            <span class="amount-value">{{ $payment->bank_code }}</span>
        </div>
        @endif
        @if($payment->bank_ac_no)
        <div class="amount-row">
            <span class="amount-label">Bank Account:</span>
            <span class="amount-value">{{ $payment->bank_ac_no }}</span>
        </div>
        @endif
        @if($payment->narration)
        <div class="amount-row">
            <span class="amount-label">Narration:</span>
            <span class="amount-value">{{ $payment->narration }}</span>
        </div>
        @endif
    </div>

    <div class="receipt-footer">
        <div>This is a computer generated receipt</div>
        <div>Thank you for your payment</div>
    </div>

    <div class="signature">
        <div class="sig-line">
            <div class="line">Customer Signature</div>
        </div>
        <div class="sig-line">
            <div class="line">Authorized Signature</div>
        </div>
    </div>
</div>

</body>
</html>
