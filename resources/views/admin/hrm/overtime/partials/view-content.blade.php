{{-- resources/views/admin/hrm/overtime/partials/view-content.blade.php --}}

<div class="table-responsive">
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="alert alert-info">
                <strong>Date:</strong> {{ $dateFormatted }}<br>
                <strong>Total Employees:</strong> {{ $records->count() }}<br>
                <strong>Total Hours:</strong> {{ number_format($totalHours, 2) }} hrs
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success">
                <strong>Total Amount:</strong> ৳ {{ number_format($totalAmount, 2) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-warning">
                <strong>Status:</strong>
                @php
                    $due = $totalAmount - ($records->sum('paid_amount') ?? 0);
                    if ($due <= 0) {
                        echo '<span class="badge bg-success">Paid</span>';
                    } elseif (($records->sum('paid_amount') ?? 0) > 0) {
                        echo '<span class="badge bg-warning">Partial</span>';
                    } else {
                        echo '<span class="badge bg-danger">Unpaid</span>';
                    }
                @endphp
            </div>
        </div>
    </div>

    <table class="table table-sm table-bordered">
        <thead class="table-head">
            <tr>
                <th width="5%">#</th>
                <th width="25%">Employee</th>
                <th width="15%">Rate (৳/hr)</th>
                <th width="15%">Hours</th>
                <th width="20%">Amount (৳)</th>
                <th width="20%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $record->name }}</strong><br>
                    <small class="text-muted">ID: {{ $record->employee_code }}</small><br>
                    <small class="text-muted">{{ $record->designation ?? 'N/A' }}</small>
                </td>
                <td class="text-end">৳ {{ number_format($record->overtime_rate, 2) }}</td>
                <td class="text-center">{{ number_format($record->overtime_hours, 2) }} hrs</td>
                <td class="text-end">৳ {{ number_format($record->overtime_amount, 2) }}</td>
                <td>{{ $record->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-light">
            <tr>
                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                <td class="text-end fw-bold">৳ {{ number_format($totalAmount, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
