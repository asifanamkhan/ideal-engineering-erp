{{-- resources/views/admin/hrm/attendance/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .present-row {
        background-color: #d4edda !important;
    }

    .absent-row {
        background-color: #f8d7da !important;
    }

    .check-in-out {
        width: 100px;
    }

    .leave-select {
        min-width: 150px;
    }

    .action-buttons {
        white-space: nowrap;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }

    /* Bigger radio buttons */
    .big-radio {
        width: 20px;
        height: 20px;
        cursor: pointer;
        margin-right: 5px;
        vertical-align: middle;
    }

    .big-radio:checked {
        accent-color: #28a745;
    }

    .form-check-label {
        font-size: 14px;
        cursor: pointer;
        margin-right: 15px;
    }

    .form-check {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
    }

    /* DataTable search box */
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 8px;
        padding: 6px 12px;
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        width: 250px;
    }

    .remarks-textarea {
        resize: vertical;
        min-height: 60px;
        width: 100%;
    }

    .employee-info-cell {
        min-width: 200px;
    }

    .disabled-input {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-calendar-check me-2"></i> Daily Attendance</h4>
            </div>
            <div>
                <a href="{{ route('admin.hrm.attendance.report') }}" class="btn btn-info shadow-sm px-4 me-2">
                    <i class="fas fa-chart-line me-2"></i> Summary Report
                </a>
                <a href="{{ route('admin.hrm.attendance.details-report') }}"
                    class="btn btn-warning shadow-sm px-4 me-2">
                    <i class="fas fa-table me-2"></i> Details Report
                </a>
                <button type="button" id="saveAttendanceBtn" class="btn btn-success shadow-sm px-5">
                    <i class="fas fa-save me-2"></i> Save Attendance
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Date</label>
                    <input type="date" id="attendance_date" class="form-control" value="{{ $date }}"
                        max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadAttendanceBtn" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Load
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <div class="action-buttons">
                        <button type="button" id="markAllPresent" class="btn btn-sm btn-success">
                            <i class="fas fa-check-circle me-1"></i> All Present
                        </button>
                        <button type="button" id="markAllAbsent" class="btn btn-sm btn-danger">
                            <i class="fas fa-times-circle me-1"></i> All Absent
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div id="attendanceNotice" class="alert" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="noticeMessage"></span>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered attendance-table" id="attendance-table" width="100%">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="18%">Employee Info</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Check In</th>
                            <th width="8%" class="text-center">Late (min)</th>
                            <th width="10%" class="text-center">Check Out</th>
                            <th width="8%" class="text-center">Early (min)</th>
                            <th width="16%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Select date and click Load</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#attendance-daily-sidebar').addClass('active');

    let currentData = [];
    let leaveTypes = @json($leaveTypes);
    let dataTable;
    let isFutureDate = false;
    let globalDefaultCheckIn = '09:00';
    let globalDefaultCheckOut = '17:00';

    function loadAttendance() {
        var date = $('#attendance_date').val();

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
            url: "{{ route('admin.hrm.attendance.index') }}",
            type: 'GET',
            data: { date: date, ajax: true },
            success: function(response) {
                Swal.close();
                currentData = response.employees;
                isFutureDate = response.is_future;

                // Store default values globally
                globalDefaultCheckIn = response.default_check_in ? response.default_check_in.substring(0, 5) : '09:00';
                globalDefaultCheckOut = response.default_check_out ? response.default_check_out.substring(0, 5) : '17:00';

                // Check if DataTable already exists
                if ($.fn.dataTable.isDataTable('#attendance-table')) {
                    $('#attendance-table').DataTable().destroy();
                }

                // Initialize new DataTable
                dataTable = $('#attendance-table').DataTable({
                    data: currentData,
                    columns: [
                        { data: null, render: function(data, type, row, meta) { return meta.row + 1; }, orderable: false },
                        { data: null, render: function(data) { return renderEmployeeInfo(data); }, orderable: true },
                        { data: null, render: function(data) { return renderStatusRadio(data); }, orderable: false, className: 'text-center' },
                        { data: null, render: function(data) { return renderCheckIn(data); }, orderable: false, className: 'text-center' },
                        { data: null, render: function(data) { return renderLateMinutes(data); }, orderable: false, className: 'text-center' },
                        { data: null, render: function(data) { return renderCheckOut(data); }, orderable: false, className: 'text-center' },
                        { data: null, render: function(data) { return renderEarlyMinutes(data); }, orderable: false, className: 'text-center' },
                        { data: null, render: function(data) { return renderRemarks(data); }, orderable: false }
                    ],
                    order: [[1, 'asc']],
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    language: {
                        search: "🔍 Search Employee:",
                        searchPlaceholder: "Type name, ID or designation...",
                        lengthMenu: "Show _MENU_ entries",
                        zeroRecords: "No employees found",
                        info: "Showing _START_ to _END_ of _TOTAL_ employees",
                        infoEmpty: "No employees available",
                        infoFiltered: "(filtered from _MAX_ total employees)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    },
                    drawCallback: function() {
                        bindEvents();
                        updateRowColors();
                    }
                });

                if (response.is_future) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'You cannot add attendance for future dates!',
                        confirmButtonColor: '#3085d6'
                    });
                    $('#saveAttendanceBtn').prop('disabled', true);
                } else {
                    $('#saveAttendanceBtn').prop('disabled', false);
                }

                if (response.attendance_exists && !response.is_future) {
                    var lastUpdated = response.last_updated || 'Unknown';
                    $('#noticeMessage').html(`Attendance for <strong>${response.date}</strong> has already been saved. Last updated: <strong>${lastUpdated}</strong>. You can edit it.`);
                    $('#attendanceNotice').removeClass('alert-warning alert-info').addClass('alert-info').show();
                } else if (!response.is_future) {
                    $('#noticeMessage').html(`Attendance for <strong>${response.date}</strong> has <strong>NOT</strong> been saved yet. Please save your attendance.`);
                    $('#attendanceNotice').removeClass('alert-info alert-warning').addClass('alert-warning').show();
                } else {
                    $('#attendanceNotice').hide();
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Failed to load attendance data', 'error');
            }
        });
    }

    function renderEmployeeInfo(emp) {
        var designation = emp.designation || 'N/A';
        return `
            <div class="employee-info-cell">
                <div><strong>${escapeHtml(emp.name)}</strong></div>
                <div><small class="text-muted">ID: ${escapeHtml(emp.employee_id)}</small></div>
                <div><small class="text-muted">Designation: ${escapeHtml(designation)}</small></div>
            </div>
        `;
    }

    function renderStatusRadio(emp) {
        if (isFutureDate) {
            return '<div class="text-center text-muted">Not allowed</div>';
        }

        var status = emp.status || 'present';
        var isPresent = (status === 'present');
        var presentChecked = isPresent ? 'checked' : '';
        var absentChecked = !isPresent ? 'checked' : '';

        return `
            <div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="status_${emp.id}" class="form-check-input status-radio big-radio" data-id="${emp.id}" value="present" ${presentChecked}>
                    <label class="form-check-label">Present</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="status_${emp.id}" class="form-check-input status-radio big-radio" data-id="${emp.id}" value="absent" ${absentChecked}>
                    <label class="form-check-label">Absent</label>
                </div>
            </div>
        `;
    }

    function renderCheckIn(emp) {
    var status = emp.status || 'present';
    var isPresent = (status === 'present');
    var checkIn = globalDefaultCheckIn;

    if (emp.check_in && emp.check_in !== '00:00:00' && emp.check_in !== '00:00') {
        checkIn = emp.check_in.toString().substring(0, 5);
    }

    var disabled = isPresent ? '' : 'disabled';
    return `<input type="time" class="form-control form-control-sm check-in-input text-center" data-id="${emp.id}" value="${checkIn}" ${disabled} style="width: 100px; margin: 0 auto;">`;
}

function renderCheckOut(emp) {
    var status = emp.status || 'present';
    var isPresent = (status === 'present');
    var checkOut = globalDefaultCheckOut;

    if (emp.check_out && emp.check_out !== '00:00:00' && emp.check_out !== '00:00') {
        checkOut = emp.check_out.toString().substring(0, 5);
    }

    var disabled = isPresent ? '' : 'disabled';
    return `<input type="time" class="form-control form-control-sm check-out-input text-center" data-id="${emp.id}" value="${checkOut}" ${disabled} style="width: 100px; margin: 0 auto;">`;
}

    function renderLeaveType(emp) {
        if (isFutureDate) {
            return '<div class="text-center text-muted">-</div>';
        }

        var status = emp.status || 'present';
        var isPresent = (status === 'present');
        var leaveTypeId = emp.leave_type_id || '';
        var disabled = isPresent ? 'disabled' : '';

        var options = '<option value="">-- Select Leave Type --</option>';
        $.each(leaveTypes, function(i, type) {
            var selected = (leaveTypeId == type.id) ? 'selected' : '';
            var paidBadge = type.is_paid == 1 ? '(Paid)' : '(Unpaid)';
            options += `<option value="${type.id}" ${selected}>${escapeHtml(type.name)} ${paidBadge}</option>`;
        });

        return `
            <select class="form-control form-control-sm leave-type-select" data-id="${emp.id}" ${disabled}>
                ${options}
            </select>
        `;
    }

    function renderRemarks(emp) {
        if (isFutureDate) {
            return '<div class="text-center text-muted">-</div>';
        }

        var remarks = emp.remarks || '';
        return `
            <textarea class="form-control remarks-textarea remarks-input" data-id="${emp.id}" rows="2" placeholder="Enter remarks...">${escapeHtml(remarks)}</textarea>
        `;
    }

    function updateRowColors() {
        $('.status-radio').each(function() {
            var row = $(this).closest('tr');
            if ($(this).val() === 'present' && $(this).is(':checked')) {
                row.removeClass('absent-row').addClass('present-row');
            } else if ($(this).val() === 'absent' && $(this).is(':checked')) {
                row.removeClass('present-row').addClass('absent-row');
            }
        });
    }

    function bindEvents() {
        $('.status-radio').off('change').on('change', function() {
            var id = $(this).data('id');
            var isPresent = $(this).val() === 'present';

            $(`.check-in-input[data-id="${id}"]`).prop('disabled', !isPresent);
            $(`.check-out-input[data-id="${id}"]`).prop('disabled', !isPresent);
            $(`.leave-type-select[data-id="${id}"]`).prop('disabled', isPresent);

            var index = currentData.findIndex(emp => emp.id == id);
            if (index !== -1) {
                currentData[index].status = $(this).val();
            }

            var row = $(this).closest('tr');
            if (isPresent) {
                row.removeClass('absent-row').addClass('present-row');
            } else {
                row.removeClass('present-row').addClass('absent-row');
            }
        });

        $('.check-in-input').off('change').on('change', function() {
            var id = $(this).data('id');
            var value = $(this).val();
            var index = currentData.findIndex(emp => emp.id == id);
            if (index !== -1) {
                currentData[index].check_in = value;
            }
        });

        $('.check-out-input').off('change').on('change', function() {
            var id = $(this).data('id');
            var value = $(this).val();
            var index = currentData.findIndex(emp => emp.id == id);
            if (index !== -1) {
                currentData[index].check_out = value;
            }
        });

        $('.leave-type-select').off('change').on('change', function() {
            var id = $(this).data('id');
            var value = $(this).val();
            var index = currentData.findIndex(emp => emp.id == id);
            if (index !== -1) {
                currentData[index].leave_type_id = value;
            }
        });

        $('.remarks-input').off('change').on('change', function() {
            var id = $(this).data('id');
            var value = $(this).val();
            var index = currentData.findIndex(emp => emp.id == id);
            if (index !== -1) {
                currentData[index].remarks = value;
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    $('#markAllPresent').click(function() {
        if (isFutureDate) {
            Swal.fire('Warning', 'Cannot mark attendance for future dates', 'warning');
            return;
        }
        $('.status-radio[value="present"]').prop('checked', true).trigger('change');
    });

    $('#markAllAbsent').click(function() {
        if (isFutureDate) {
            Swal.fire('Warning', 'Cannot mark attendance for future dates', 'warning');
            return;
        }
        $('.status-radio[value="absent"]').prop('checked', true).trigger('change');
    });

    $('#saveAttendanceBtn').click(function() {
    if (isFutureDate) {
        Swal.fire('Error', 'Cannot save attendance for future dates!', 'error');
        return;
    }

    var date = $('#attendance_date').val();
    var attendanceData = [];

    $.each(currentData, function(index, emp) {
        var checkIn = globalDefaultCheckIn;
        var checkOut = globalDefaultCheckOut;

        if (emp.check_in && emp.check_in !== '00:00' && emp.check_in !== '00:00:00') {
            checkIn = emp.check_in.toString().substring(0, 5);
        }

        if (emp.check_out && emp.check_out !== '00:00' && emp.check_out !== '00:00:00') {
            checkOut = emp.check_out.toString().substring(0, 5);
        }

        // If status is absent, set check-in/out to null
        if (emp.status === 'absent') {
            checkIn = null;
            checkOut = null;
        }

        var data = {
            employee_id: emp.id,
            status: emp.status || 'present',
            check_in: checkIn,
            check_out: checkOut,
            remarks: emp.remarks || null
        };
        attendanceData.push(data);
    });

    console.log('Sending data:', attendanceData);

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "{{ route('admin.hrm.attendance.store') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            date: date,
            attendance: attendanceData
        },
        success: function(response) {
            Swal.close();
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });
            loadAttendance();
        },
        error: function(xhr) {
            Swal.close();
            console.log('Error response:', xhr);
            var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save attendance';
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg
            });
        }
    });
});

    $('#loadAttendanceBtn').click(function() {
        loadAttendance();
    });

    $('#attendance_date').on('change', function() {
        var selectedDate = $(this).val();
        var today = new Date().toISOString().split('T')[0];

        if (selectedDate > today) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'You cannot select future dates!',
                confirmButtonColor: '#d33'
            });
            $(this).val(today);
            return;
        }
        loadAttendance();
    });

    loadAttendance();

    function renderLateMinutes(emp) {
        if (isFutureDate) return '<div class="text-center text-muted">-</div>';

        var lateMinutes = emp.late_minutes || 0;
        if (lateMinutes > 0) {
            return '<span class="badge bg-warning text-dark">' + lateMinutes + ' min</span>';
        }
        return '<span class="badge bg-success">0</span>';
    }

    function renderEarlyMinutes(emp) {
        if (isFutureDate) return '<div class="text-center text-muted">-</div>';

        var earlyMinutes = emp.early_minutes || 0;
        if (earlyMinutes > 0) {
            return '<span class="badge bg-danger">' + earlyMinutes + ' min</span>';
        }
        return '<span class="badge bg-success">0</span>';
    }
</script>
@endsection
