<div class="quotation-wrapper no-page-break">
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
            <span style="font-weight:bold;">Date:</span> {{ date('d/m/Y', strtotime($job->quotation->quotation_date ?? $job->date)) }}
        </div>
    </div>
    <span><strong>Subject:</strong> {{ $job->quotation_subject ?? '' }}</span>
    <div class="quotation-label">QUOTATION</div>

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
            @if(!empty($job->quotation->items) && count($job->quotation->items) > 0)
                @foreach($job->quotation->items as $key => $item)
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
                    <td colspan="6" style="text-align:center;">No items found</td
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $vatPercent = $job->quotation->quotation_vat ?? 0;
        $vatType = $job->quotation->quotation_vat_type ?? 'include';

        if ($vatType == 'include' && $vatPercent > 0) {
            $vatAmount = ($subtotal * $vatPercent) / (100 + $vatPercent);
            $totalAmount = $subtotal;
        } else {
            $vatAmount = ($subtotal * $vatPercent) / 100;
            $totalAmount = $subtotal + $vatAmount;
        }
    @endphp

    <table class="total-section">
        <tr>
            <td class="lbl">Subtotal:</td>
            <td class="val">{{ number_format($subtotal, 2) }}</td>
        </tr>

        @if($vatPercent > 0)
        <tr>
            <td class="lbl">
                @if($vatType == 'include')
                    VAT ({{ $vatPercent }}% Included):
                @else
                    VAT ({{ $vatPercent }}%):
                @endif
            </td>
            <td class="val">
                @if($vatType == 'include')
                    ({{ number_format($vatAmount, 2) }})
                @else
                    {{ number_format($vatAmount, 2) }}
                @endif
            </td>
        </tr>
        @endif

        <tr style="border-top:2px solid #000;">
            <td class="lbl" style="font-size:14px;">
                Grand Total
                @if($vatType == 'include')
                    <span style="font-size: 11px; font-weight: normal;">(Incl. VAT)</span>
                @endif
            </td>
            <td class="val" style="font-size:14px;">{{ number_format($totalAmount, 2) }}</td>
        </tr>
    </table>

    <div class="words-section">
        <strong>In Words:</strong> {{ App\Helpers\NumberToWords::convert($totalAmount, 'Taka') }}.
        <p><strong>Comments:</strong> {{ $job->quotation_description ?? '' }}</p>
    </div>

    <div class="bill-footer">
        <div style="border-top:1px solid #000;padding-top:4px;">Customer Signature</div>
        <div class="sig-right">
            @if($invoice_settings && $invoice_settings->author_signature)
                <div>
                    <img src="{{ asset($invoice_settings->author_signature) }}" alt="Signature" style="max-height: 40px;">
                </div>
            @endif
            <div style="border-top:1px solid #000;padding-top:4px;"></div>
            <div style="margin-top:4px;">Authorized Signature</div>
        </div>
    </div>
</div>
@if($invoice_settings && $invoice_settings->footer_text)
<div class="invoice-footer" style="margin-top: 25px; text-align: center; font-size: 11px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
    {!! $invoice_settings->footer_text !!}
</div>
@endif
