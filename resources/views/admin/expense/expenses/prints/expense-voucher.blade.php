<div class="expense-wrapper no-page-break">
    @include('admin.partials.print-header')

    <div class="to-section">
        <div>
            <div>Expense Voucher</div>
            <div style="margin-left:16px;">Date: {{ date('d/m/Y', strtotime($expense->date ?? now())) }}</div>
            @if($expense->narration)
            <div style="margin-left:16px;">Narration: {{ $expense->narration }}</div>
            @endif
        </div>
        <div>
            <span style="font-weight:bold;">Expense No:</span>
            <span style="font-weight:bold;">
                {{ $expense->expense_no ?? 'N/A' }}
            </span>
        </div>
    </div>

    <div class="quotation-label">EXPENSE DETAILS</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 20%">Sl No</th>
                <th style="width: 50%">Category</th>
                <th style="width: 30%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @if(!empty($expense->items) && count($expense->items) > 0)
                @foreach($expense->items as $key => $item)
                    @php
                        $total += $item->amount ?? 0;
                    @endphp
                    <tr>
                        <td style="text-align:center;">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td style="text-align:left;">{{ $item->category_name ?? 'N/A' }}</td>
                        <td style="text-align:right;">{{ number_format($item->amount ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" style="text-align:center;">No expense items found</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="total-section">
        <tr style="border-top:2px solid #000;">
            <td class="lbl" style="font-size:14px;">Total Amount=</td>
            <td class="val" style="font-size:14px;">{{ number_format($total, 2) }}</td>
        </tr>
        @if($expense->paid_amount && $expense->paid_amount > 0)
        <tr>
            <td class="lbl">Paid Amount=</td>
            <td class="val">{{ number_format($expense->paid_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="lbl">Due Amount=</td>
            <td class="val">{{ number_format($total - ($expense->paid_amount ?? 0), 2) }}</td>
        </tr>
        @endif
    </table>

    <div class="words-section">
        <strong>In Words:</strong> {{ App\Helpers\NumberToWords::convert($total, 'Taka') }} Taka only.
    </div>

    <div class="bill-footer">
        <div style="border-top:1px solid #000;padding-top:4px;">Received by</div>
        <div class="sig-right">
            <div style="border-top:1px solid #000;padding-top:4px;"></div>
            <div style="margin-top:4px;">Authorized Signature</div>
        </div>
    </div>
</div>
