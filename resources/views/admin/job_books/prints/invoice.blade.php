<div class="invoice-wrapper no-page-break">
    @include('admin.partials.print-header', ['logo' => ''])

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
        $totalAmount = $subtotal - $discount;
        $paidAmount = $job->invoice->invoice_paid_amount ?? 0;
        $dueAmount = $totalAmount - $paidAmount;

        // Payment Status
        $paymentStatus = 'UNPAID';
        if ($paidAmount >= $totalAmount) {
            $paymentStatus = 'PAID';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'PARTIAL PAID';
        }
    @endphp

    <!-- Totals Table with Payment Status in Left Cell (rowspan) -->
    <table class="total-section" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        @php
            $rowCount = 1; // for subtotal
            if ($discount > 0) $rowCount++;
            $rowCount++; // for grand total
            $rowCount++; // for paid amount
            $rowCount++; // for due amount
        @endphp

        <!-- Payment Status Row with rowspan -->
        <tr>
            <td rowspan="{{ $rowCount }}" style="width: 70%; text-align: left; padding: 6px; vertical-align: top;">
                <div style="border: 2px solid #000; padding: 4px 12px; font-weight: bold; font-size: 12px; display: inline-block;">
                    {{ $paymentStatus }}
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

        <!-- Grand Total -->
        <tr style="border-top: 2px solid #000;">
            <td style="text-align: right; padding: 8px; font-weight: bold; font-size: 14px;">Grand Total:</td>
            <td style="text-align: right; padding: 8px; font-weight: bold; font-size: 14px;">{{ number_format($totalAmount, 2) }}</td>
        </tr>

        <!-- Paid Amount -->
        <tr style="border-top: 1px solid #000;">
            <td style="text-align: right; padding: 6px; font-weight: bold;">Paid Amount:</td>
            <td style="text-align: right; padding: 6px;">{{ number_format($paidAmount, 2) }}</td>
        </tr>

        <!-- Due Amount -->
        <tr>
            <td style="text-align: right; padding: 6px; font-weight: bold;">Due Amount:</td>
            <td style="text-align: right; padding: 6px; border-bottom: 1px solid #ddd;">{{ number_format($dueAmount, 2) }}</td>
        </tr>
    </table>

    <div class="words-section" style="margin-top: 15px;">
        <strong>In Words:</strong> {{ App\Helpers\NumberToWords::convert($totalAmount, 'Taka') }} Taka only.
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
