{{-- resources/views/admin/hrm/overtime/employee-wise.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>
    .overtime-table th {
        font-weight: 600;
        text-align: center;
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

    .weekend-row {
        background-color: #f8d7da !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-user-clock me-2"></i> Employee-wise Overtime Entry</h4>
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
                <div class="col-md-4">
                    <label class="form-label fw-bold">Select Employee <span class="text-danger">*</span></label>
                    <select id="employee_id" class="form-control select2-employee" style="width: 100%;">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month <span class="text-danger">*</span></label>
                    <input type="month" id="month_year" class="form-control" value="{{ date('Y-m') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadDetailsBtn" class="btn btn-primary w-100">
                        <i class="fas fa-refresh me-2"></i> Load
                    </button>
                </div>
            </div>

            <!-- Warning Message -->
            <div id="weekendNote" class="alert alert-info" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <span id="weekendNoteMessage"></span>
            </div>

            <div id="employeeInfo" style="display: none;">
                <div class="alert alert-info">
                    <strong>Employee:</strong> <span id="emp_name"></span> (<span id="emp_code"></span>)
                    <br><strong>Default Overtime Rate:</strong> ৳ <span id="emp_rate"></span>/hour
                    <br><strong>Note:</strong> <span class="text-danger">Weekend days have rate set to 0 by default. You
                        can manually change if needed.</span>
                </div>
            </div>
            <div class="alert alert-info" id="dateLimitInfo" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Only dates up to <span id="todayDate"></span> are displayed. You cannot add
                overtime for future dates.
            </div>
            <div id="overtimeContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered overtime-table" id="overtime-table">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Date</th>
                                <th width="12%">Day & Status</th>
                                <th width="15%">Rate (৳/hr)</th>
                                <th width="15%">Hours</th>
                                <th width="15%">Amount (৳)</th>
                                <th width="28%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="overtime-tbody">
                            <tr>
                                <td colspan="7" class="text-center">Select employee and month, then click Load</td>
                            </tr>
                        </tbody>
                        <tfoot id="overtime-tfoot" style="display: none;">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Total Overtime Amount:</td>
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
    <input type="hidden" name="employee_id" id="form_employee_id">
    <input type="hidden" name="month_year" id="form_month_year">
    <input type="hidden" name="overtime_data" id="form_overtime_data">
</form>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#overtime-employee-wise-sidebar').addClass('active');

    let overtimeData = [];
    let employeeData = null;
    let weekendDays = [];

    // Initialize Select2
    $('.select2-employee').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search employee...',
        width: '100%'
    });

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
        var employeeId = $('#employee_id').val();
        var monthYear = $('#month_year').val();

        if (!employeeId) {
            Swal.fire('Error', 'Please select an employee', 'error');
            return;
        }
        if (!monthYear) {
            Swal.fire('Error', 'Please select a month', 'error');
            return;
        }

        Swal.fire({
            title: 'Loading...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.overtime.get-employee-wise-details') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                employee_id: employeeId,
                month_year: monthYear
            },
            success: function(response) {
                Swal.close();
                employeeData = response.employee;
                overtimeData = response.data;
                weekendDays = response.weekends || [];

                $('#emp_name').text(employeeData.name);
                $('#emp_code').text(employeeData.employee_id);
                $('#emp_rate').text(employeeData.overtime_rate || 0);
                $('#employeeInfo').show();
                $('#dateLimitInfo').show();

                // Show weekend note
                if (response.weekends && response.weekends.length > 0) {
                    var weekendNames = response.weekend_names || 'Friday, Saturday';
                    $('#weekendNote').show();
                    $('#weekendNoteMessage').text(`⚠️ Note: Weekend days (${weekendNames}) have overtime rate set to 0 by default. You can manually change the rate if needed.`);
                } else {
                    $('#weekendNote').hide();
                }

                renderOvertimeTable();
                $('#overtimeContainer').show();
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Failed to load data', 'error');
            }
        });
    });

    function renderOvertimeTable() {
    var tbody = $('#overtime-tbody');
    tbody.empty();

    if (overtimeData.length === 0) {
        tbody.append('<tr><td colspan="8" class="text-center text-muted">No data found</td></tr>');
        $('#overtime-tfoot').hide();
        return;
    }

    $.each(overtimeData, function(index, item) {
        var isWeekend = item.is_weekend || false;
        var rowClass = isWeekend ? 'weekend-row' : '';
        var rateBgColor = isWeekend ? 'background-color: #f5c6cb;' : '';
        var rateValue = item.overtime_rate || 0;
        var canEdit = item.can_add_overtime === true;

        // Attendance status badge
        var attendanceStatusText = '';
        if (item.attendance_status === 'present') {
            attendanceStatusText = '<span class="badge bg-success">Present</span>';
        } else if (item.attendance_status === 'absent') {
            attendanceStatusText = '<span class="badge bg-danger">Absent</span>';
        } else if (item.attendance_status === 'on_leave') {
            attendanceStatusText = '<span class="badge bg-info">On Leave</span>';
        } else {
            attendanceStatusText = '<span class="badge bg-secondary">No Record</span>';
        }

        var dayName = item.day_name;
        if (isWeekend) {
            dayName = item.day_name + ' (Weekend)';
        }

        // Disable inputs if not can edit (absent, on leave, or weekend)
        var rateDisabled = (!canEdit || isWeekend) ? 'disabled' : '';
        var hoursDisabled = (!canEdit || isWeekend) ? 'disabled' : '';
        var remarksDisabled = (!canEdit || isWeekend) ? 'disabled' : '';

        var row = `
            <tr data-index="${index}" class="${rowClass}">
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${item.date}</td>
                <td class="text-center">
                    ${dayName}<br>
                    ${attendanceStatusText}
                </td>
                <td class="text-center">
                    <input type="number" step="1" class="form-control form-control-sm overtime-rate text-end editable-input" value="${rateValue}" style="width: 100px; ${rateBgColor}" ${rateDisabled}>
                </td>
                <td class="text-center">
                    <input type="number" step="0.5" class="form-control form-control-sm overtime-hours text-end editable-input" value="${item.overtime_hours}" style="width: 80px;" ${hoursDisabled}>
                </td>
                <td class="text-center">
                    <input type="hidden" class="overtime-amount" value="${item.overtime_amount}">
                    <span class="amount-display fw-bold">৳ ${parseFloat(item.overtime_amount).toFixed(2)}</span>
                </td>
                <td class="text-center">
                    <textarea class="form-control form-control-sm remarks-input" rows="1" placeholder="Optional remarks..." style="width: 100%;" ${remarksDisabled}>${escapeHtml(item.remarks || '')}</textarea>
                    <input type="hidden" class="record-id" value="${item.record_id || ''}">
                    <input type="hidden" class="item-date" value="${item.date}">
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Bind events only for editable fields
    $('.overtime-rate:not(:disabled), .overtime-hours:not(:disabled)').on('input', function() {
        calculateRowAmount($(this).closest('tr'));
    });

    calculateGrandTotal();
    $('#overtime-tfoot').show();
}

$('#saveOvertimeBtn').click(function() {
    var employeeId = $('#employee_id').val();
    var monthYear = $('#month_year').val();
    var overtimeDataToSave = [];

    $('.overtime-table tbody tr').each(function() {
        var row = $(this);
        var hours = parseFloat(row.find('.overtime-hours').val()) || 0;

        overtimeDataToSave.push({
            date: row.find('.item-date').val(),
            overtime_rate: parseFloat(row.find('.overtime-rate').val()) || 0,
            overtime_hours: hours,  // This will be 0 if user set to 0
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
        url: "{{ route('admin.hrm.overtime.store-employee-wise') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            employee_id: employeeId,
            month_year: monthYear,
            overtime_data: overtimeDataToSave
        },
        success: function(response) {
            Swal.close();
            console.log('Success response:', response);  // Debug line
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
            console.log('Error response:', xhr);  // Debug line
            var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save';
            Swal.fire('Error', errorMsg, 'error');
        }
    });
});

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
</script>
@endsection
