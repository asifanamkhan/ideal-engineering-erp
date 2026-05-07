
@extends('layouts.dashboard.app')

@section('css')
<style>
    .overtime-table th {
        font-weight: 600;
        text-align: center;
    }

    .alert-warning, .alert-info {
        border-radius: 8px;
        font-size: 13px;
    }

    .overtime-table td {
        vertical-align: middle;
        text-align: center;
    }

    .overtime-table input {
        text-align: right;
        font-size: 12px;
    }

    .editable-input {
        background-color: #fff3cd;
    }

    .total-row {
        background-color: #e9ecef;
        font-weight: bold;
    }
</style>
@endsection

{{-- resources/views/admin/hrm/overtime/date-wise.blade.php --}}

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-calendar-day me-2"></i> Date-wise Overtime Entry</h4>
            </div>
            <div>
                <a href="{{ route('admin.hrm.overtime.index') }}" class="btn btn-secondary shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Date <span class="text-danger">*</span></label>
                    <input type="date" id="overtime_date" class="form-control" value="{{ date('Y-m-d') }}"
                        max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadDetailsBtn" class="btn btn-primary w-100">
                        <i class="fas fa-refresh me-2"></i> Load
                    </button>
                </div>
            </div>

            <!-- Warning Message for Weekend -->
            <div id="weekendWarning" class="alert alert-warning" style="display: none;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="weekendWarningMessage"></span>
            </div>

            <!-- Warning Message for Future Date -->
            <div id="futureDateWarning" class="alert alert-danger" style="display: none;">
                <i class="fas fa-ban me-2"></i>
                You cannot add overtime for future dates! Please select a date on or before today.
            </div>

            <!-- Warning Message for Leave -->
            <div id="leaveWarning" class="alert alert-info" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <span id="leaveWarningMessage"></span>
            </div>

            <div id="overtimeContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered overtime-table" id="overtime-table">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Employee Info</th>
                                <th width="15%">Rate (৳/hr)</th>
                                <th width="15%">Hours</th>
                                <th width="15%">Amount (৳)</th>
                                <th width="25%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="overtime-tbody">
                            <tr>
                                <td colspan="6" class="text-center">Select date and click Load</td>
                            </tr>
                        </tbody>
                        <tfoot id="overtime-tfoot" style="display: none;">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold" id="grandTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="button" id="saveOvertimeBtn" class="btn btn-lg btn-success px-5">
                            <i class="fas fa-save me-2"></i> Save Overtime
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="overtimeForm" method="POST">
    @csrf
    <input type="hidden" name="date" id="form_date">
    <input type="hidden" name="overtime_data" id="form_overtime_data">
</form>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#overtime-date-wise-sidebar').addClass('active');

    let overtimeData = [];

    function calculateRowAmount(row) {
        var rate = parseFloat($(row).find('.overtime-rate').val()) || 0;
        var hours = parseFloat($(row).find('.overtime-hours').val()) || 0;
        var amount = rate * hours;

        $(row).find('.overtime-amount').val(amount.toFixed(2));
        $(row).find('.amount-display').text('৳ ' + amount.toFixed(2));

        var index = $(row).data('index');
        if (overtimeData[index]) {
            overtimeData[index].overtime_rate = rate;
            overtimeData[index].overtime_hours = hours;
            overtimeData[index].overtime_amount = amount;
        }

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        var total = 0;
        $('.overtime-amount').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#grandTotal').text(total.toFixed(2));
    }

    $('#loadDetailsBtn').click(function() {
    var date = $('#overtime_date').val();

    if (!date) {
        Swal.fire('Error', 'Please select a date', 'error');
        return;
    }

    Swal.fire({
        title: 'Loading...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "{{ route('admin.hrm.overtime.get-date-wise-details') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            date: date
        },
        success: function(response) {
            Swal.close();

            // Show/hide warnings
            if (response.is_weekend) {
                $('#weekendWarning').show();
                $('#weekendWarningMessage').text(response.weekend_message || '⚠️ Warning: This date is a weekend! Overtime rate has been set to 0 by default. You can still change it manually if needed.');
            } else {
                $('#weekendWarning').hide();
            }

            if (response.excluded_employees && response.excluded_employees.length > 0) {
                var excludedNames = response.excluded_employees.join(', ');
                $('#leaveWarning').show();
                $('#leaveWarningMessage').html(`<strong>⚠️ Note:</strong> The following employees are not shown (Absent/On Leave): ${excludedNames}`);
            } else {
                $('#leaveWarning').hide();
            }

            overtimeData = response.data;
            renderOvertimeTable();
            $('#overtimeContainer').show();
        },
        error: function(xhr) {
            Swal.close();

            // Check if response has custom error message
            if (xhr.responseJSON && xhr.responseJSON.error) {
                var errorMsg = xhr.responseJSON.error;

                // Check if attendance missing
                if (xhr.responseJSON.attendance_missing) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Attendance Required!',
                        text: errorMsg,
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg,
                        confirmButtonColor: '#d33'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load overtime data. Please try again.',
                    confirmButtonColor: '#d33'
                });
            }

            // Clear the container
            $('#overtimeContainer').hide();
            $('#overtime-tbody').html('<tr><td colspan="6" class="text-center text-danger">' + (xhr.responseJSON?.error || 'Failed to load data') + '</td></tr>');
        }
    });
});

function renderOvertimeTable() {
    var tbody = $('#overtime-tbody');
    tbody.empty();

    if (overtimeData.length === 0) {
        tbody.append('<tr><td colspan="6" class="text-center text-muted">No employees found</td></tr>');
        $('#overtime-tfoot').hide();
        return;
    }

    $.each(overtimeData, function(index, emp) {
        var isWeekend = emp.is_weekend || false;
        var rateBgColor = isWeekend ? 'background-color: #f5c6cb;' : '';
        var rowClass = isWeekend ? 'weekend-row' : '';
        var rateValue = emp.overtime_rate || 0;

        var row = `
            <tr data-index="${index}" class="${rowClass}">
                <td class="text-center">${index + 1}</td>
                <td class="" style="text-align: left;">
                    <div>
                        <strong>${escapeHtml(emp.employee_name)}</strong><br>
                        ID:<small class="text-muted">ID: ${emp.employee_code}</small>,
                        DES:<small class="text-muted">${emp.designation}</small>
                    </div>
                    <input type="hidden" class="employee-id" value="${emp.employee_id}">
                    <input type="hidden" class="record-id" value="${emp.record_id || ''}">
                </td>
                <td class="text-center">
                    <input type="number" step="1" class="form-control form-control-sm overtime-rate text-end editable-input" value="${rateValue}" style="width: 100px; ${rateBgColor}">
                </td>
                <td class="text-center">
                    <input type="number" step="0.5" class="form-control form-control-sm overtime-hours text-end editable-input" value="${emp.overtime_hours}" style="width: 80px;">
                </td>
                <td class="text-center">
                    <input type="hidden" class="overtime-amount" value="${emp.overtime_amount}">
                    <span class="amount-display fw-bold">৳ ${parseFloat(emp.overtime_amount).toFixed(2)}</span>
                </td>
                <td class="text-center">
                    <textarea class="form-control form-control-sm remarks-input" rows="1" placeholder="Optional remarks..." style="width: 100%;">${escapeHtml(emp.remarks || '')}</textarea>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Bind events for both rate and hours (editable for all)
    $('.overtime-rate, .overtime-hours').on('input', function() {
        calculateRowAmount($(this).closest('tr'));
    });

    calculateGrandTotal();
    $('#overtime-tfoot').show();
}

    $('#saveOvertimeBtn').click(function() {
        var date = $('#overtime_date').val();
        var overtimeDataToSave = [];

        $('.overtime-table tbody tr').each(function() {
            var row = $(this);
            overtimeDataToSave.push({
                employee_id: row.find('.employee-id').val(),
                overtime_rate: parseFloat(row.find('.overtime-rate').val()) || 0,
                overtime_hours: parseFloat(row.find('.overtime-hours').val()) || 0,
                remarks: row.find('.remarks-input').val() || null,
                record_id: row.find('.record-id').val() || null
            });
        });

        Swal.fire({
            title: 'Saving...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.overtime.store-date-wise') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                date: date,
                overtime_data: overtimeDataToSave
            },
            success: function(response) {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    $('#loadDetailsBtn').click();
                });
            },
            error: function(xhr) {
                Swal.close();
                var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save';
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    $('#overtime_date').on('change', function() {
    var selectedDate = $(this).val();
    var today = new Date().toISOString().split('T')[0];

    if (selectedDate > today) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Date',
            text: 'You cannot add overtime for future dates!',
            confirmButtonColor: '#d33'
        });
        $(this).val(today);
        return;
    }

    // Clear previous data
    $('#overtimeContainer').hide();
    $('#overtime-tbody').html('<tr><td colspan="6" class="text-center text-muted">Select date and click Load</td></tr>');
});
</script>
@endsection
