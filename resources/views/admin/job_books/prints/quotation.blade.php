<div class="quotation-wrapper no-page-break">
    @include('admin.partials.print-header', ['logo' => 'uploads/logo.png'])

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
            <span style="font-weight:bold;">Job ID:</span> {{ $job->job_id ?? 'N/A' }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="font-weight:bold;">Date:</span> {{ date('d/m/Y', strtotime($job->quotation->quotation_date ?? $job->date)) }}
        </div>
    </div>

    <div class="quotation-label">QUOTATION</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:10%">Sl No</th>
                <th style="width:40%">Part Name</th>
                <th style="width:10%">Size</th>
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
                    <td colspan="5" style="text-align:center;">No items found</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="total-section">
        <tr>
            <td class="lbl">Total Amount=</td>
            <td class="val">{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr style="border-top:2px solid #000;">
            <td class="lbl" style="font-size:14px;">Grand Total=</td>
            <td class="val" style="font-size:14px;">{{ number_format($subtotal, 2) }}</td>
        </tr>
    </table>

    <div class="words-section">
        <strong>In Words:</strong> {{ App\Helpers\NumberToWords::convert($subtotal, 'Taka') }} Taka only.
    </div>

    <div class="bill-footer">
        <div style="border-top:1px solid #000;padding-top:4px;">Customer Signature</div>
        <div class="sig-right">
            <div style="border-top:1px solid #000;padding-top:4px;"></div>
            <div style="margin-top:4px;">Authorized Signature</div>
        </div>
    </div>
</div>
