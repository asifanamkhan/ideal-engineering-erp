<div class="invoice-wrapper no-page-break">
    @php
        $invoice_settings = DB::table('invoice_settings')->first();
    @endphp
    @include('admin.partials.print-header', ['logo' => 'uploads/logo.png','invoice_settings' => $invoice_settings])

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
            <span style="font-weight:bold;">Date:</span> {{ date('d/m/Y', strtotime($job->invoice->invoice_date ?? $job->date)) }}
        </div>
    </div>

    <div class="quotation-label">INVOICE</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:10%">Sl No</th>
                <th style="width:40%">Service Name</th>
                <th style="width:10%">Unit</th>
                <th style="width:10%">Qty</th>
                <th style="width:15%">Unit Price</th>
                <th style="width:15%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotal = 0; @endphp
            @if(!empty($job->invoice->items) && count($job->invoice->items) > 0)
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
                        <td style="text-align:right;">{{ number_format($item->price ?? 0, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($itemTotal, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="text-align:center;">No items found</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $discount = $job->invoice->invoice_discount ?? 0;
        $transportCost = $job->invoice->invoice_transport_cost ?? 0;
        $vatPercent = $job->invoice->invoice_vat ?? 0;
        $vatType = $job->invoice->invoice_vat_type ?? 'include';

        $afterDiscount = $subtotal - $discount;
        $afterTransport = $afterDiscount + $transportCost;

        if ($vatType == 'include' && $vatPercent > 0) {
            $vatAmount = ($afterTransport * $vatPercent) / (100 + $vatPercent);
            $totalAmount = $afterTransport;
        } else {
            $vatAmount = ($afterTransport * $vatPercent) / 100;
            $totalAmount = $afterTransport + $vatAmount;
        }

        $paidAmount = $job->invoice->invoice_paid_amount ?? 0;
        $dueAmount = $totalAmount - $paidAmount;

        $paymentStatus = 'UNPAID';
        if ($paidAmount >= $totalAmount) {
            $paymentStatus = 'PAID';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'PARTIAL PAID';
        }

        $rowCount = 1;
        if ($discount > 0) $rowCount++;
        if ($transportCost > 0) $rowCount++;
        if ($vatType == 'exclude' && $vatPercent > 0) $rowCount++;
        $rowCount++;
        $rowCount++;
        $rowCount++;
    @endphp

    <table class="total-section" style="width: 100%; border-collapse: collapse; margin-top: 10px;">

        <tr>
            <td rowspan="{{ $rowCount }}" style="width: 70%; text-align: left; padding: 6px; vertical-align: top;">
                <div style="">
                    {{-- {{ $paymentStatus }} --}}
                </div>
            </td>
            <td style="width: 30%; text-align: right; padding: 6px; font-weight: bold;">Subtotal:</td>
            <td style="width: 30%; text-align: right; padding: 6px; border-bottom: 1px solid #ddd;">{{ number_format($subtotal, 2) }}</td>
        </tr>

        @if($discount > 0)
        <tr>
            <td style="text-align: right; padding: 6px; font-weight: bold;">Discount:</td>
            <td style="text-align: right; padding: 6px; border-bottom: 1px solid #ddd;">{{ number_format($discount, 2) }}</td>
        </tr>
        @endif

        @if($transportCost > 0)
        <tr>
            <td style="text-align: right; padding: 6px; font-weight: bold;">Transport Cost:</td>
            <td style="text-align: right; padding: 6px; border-bottom: 1px solid #ddd;">{{ number_format($transportCost, 2) }}</td>
        </tr>
        @endif

        @if($vatType == 'exclude' && $vatPercent > 0)
        <tr>
            <td style="text-align: right; padding: 6px; font-weight: bold;">VAT ({{ $vatPercent }}%):</td>
            <td style="text-align: right; padding: 6px; border-bottom: 1px solid #ddd;">{{ number_format($vatAmount, 2) }}</td>
        </tr>
        @endif

        <tr style="border-top: 2px solid #000;">
            <td style="text-align: right; padding: 8px; font-weight: bold; font-size: 14px;">
                Grand Total
                @if($vatType == 'include')
                    <span style="font-size: 11px; font-weight: normal;">(Incl. VAT & TAX)</span>
                @endif
            </td>
            <td style="text-align: right; padding: 8px; font-weight: bold; font-size: 14px;">{{ number_format($totalAmount, 2) }}</td>
        </tr>

        <tr style="border-top: 1px solid #000;">
            <td style="text-align: right; padding: 6px; font-weight: bold;">Paid Amount:</td>
            <td style="text-align: right; padding: 6px;">{{ number_format($paidAmount, 2) }}</td>
        </tr>

        <tr>
            <td style="text-align: right; padding: 6px; font-weight: bold;">Due Amount:</td>
            <td style="text-align: right; padding: 6px; border-bottom: 1px solid #ddd;">{{ number_format($dueAmount, 2) }}</td>
        </tr>
    </table>

    <div class="words-section" style="margin-top: 15px;">
        <strong>In Words:</strong> {{ App\Helpers\NumberToWords::convert($totalAmount, 'Taka') }}.
    </div>

    <div class="bill-footer" style="display: flex; justify-content: space-between; margin-top: 40px;">
        <div style="text-align: center; width: 40%;">
            <div style="border-top: 1px solid #000; padding-top: 5px;">Customer Signature</div>
        </div>
        <div style="text-align: center; width: 40%;">
            <div style="border-top: 1px solid #000; padding-top: 5px;">Authorized Signature</div>
        </div>
    </div>
</div>
@if($invoice_settings && $invoice_settings->footer_text)
<div class="invoice-footer" style="margin-top: 20px; text-align: center; font-size: 11px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
    {!! $invoice_settings->footer_text !!}
</div>
@endif
