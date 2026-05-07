{{-- resources/views/admin/hrm/attendance/details-report.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .attendance-matrix {
        position: relative;
        width: 100%;
    }
    .big-radio {
        width: 20px;
        height: 20px;
        cursor: pointer;
        margin-right: 5px;
        vertical-align: middle;
    }
    /* Horizontal scroll wrapper */
    .matrix-scroll-wrapper {
        overflow-x: auto;
        overflow-y: visible;
        position: relative;
        width: 100%;
        margin-bottom: 20px;
    }

    .attendance-matrix table {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
        font-size: 12px;
    }

    .attendance-matrix th {
        background-color: forestgreen;
        color: white;
        font-weight: 600;
        text-align: center;
        padding: 10px 4px;
        border: 1px solid #ddd;
        white-space: nowrap;
    }

    .attendance-matrix td {
        text-align: center;
        padding: 0px 4px;
        vertical-align: middle;
        border: 1px solid #ddd;
        white-space: nowrap;
    }

    /* Sticky first two columns */
    .attendance-matrix th:first-child,
    .attendance-matrix td:first-child {
        position: sticky;
        left: 0;
        background-color: #f8f9fc;
        z-index: 10;
    }

    .attendance-matrix th:nth-child(2),
    .attendance-matrix td:nth-child(2) {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 10;
    }

    .attendance-matrix th:first-child {
        background-color: #f0f0f0;
        left: 0;
        min-width: 40px;
    }

    .attendance-matrix th:nth-child(2) {
        background-color: #f0f0f0;
        left: 40px;
        min-width: 180px;
    }

    .attendance-matrix td:first-child {
        background-color: #f8f9fc;
        font-weight: 500;
    }

    .attendance-matrix td:nth-child(2) {
        background-color: white;
        text-align: left;
    }

    /* Color classes */
    .present-cell {
        background-color: #d4edda !important;
        cursor: pointer;
    }
    .absent-cell {
        background-color: #f8d7da !important;
        cursor: pointer;
    }
    .not-recorded-cell {
        background-color: #fff3cd !important;
        cursor: pointer;
    }
    .leave-cell {
        background-color: #cce5ff !important;
        cursor: not-allowed;
    }
    .weekend-cell {
        background-color: #e9ecef !important;
        color: #6c757d;
        cursor: not-allowed;
    }
    .present-cell:hover, .absent-cell:hover, .not-recorded-cell:hover {
        opacity: 0.8;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        min-width: 32px;
    }

    .present-cell .status-badge {
        background-color: #28a745;
        color: white;
    }
    .absent-cell .status-badge {
        background-color: #dc3545;
        color: white;
    }
    .not-recorded-cell .status-badge {
        background-color: #ffc107;
        color: #856404;
    }
    .leave-cell .status-badge {
        background-color: #0066cc;
        color: white;
    }
/* PL (Present Late) - Yellow */
.pl-cell {
    background-color: #fff3cd !important;
}
.pl-cell .status-badge {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* PE (Present Early) - Orange */
.pe-cell {
    background-color: #ffe5d9 !important;
}
.pe-cell .status-badge {
    background-color: #fd7e14 !important;
    color: white !important;
}

/* PLE (Present Late & Early) - Red */
.ple-cell {
    background-color: #f8d7da !important;
}
.ple-cell .status-badge {
    background-color: #dc3545 !important;
    color: white !important;
}
    .summary-row {
        background-color: #f8f9fc;
        font-weight: 600;
    }
    .summary-row td {
        background-color: #e9ecef;
    }

    .export-buttons {
        margin-bottom: 15px;
    }

    .employee-info-cell {
        min-width: 170px;
    }
    .employee-info-cell strong {
        font-size: 13px;
    }
    .employee-info-cell small {
        font-size: 10px;
    }

    .updated-info {
        font-size: 9px;
        color: #0066cc;
        margin-top: 2px;
    }

    /* Progress bar */
    .progress {
        height: 4px;
        width: 50px;
        border-radius: 4px;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .attendance-matrix table {
            font-size: 10px;
        }
        .attendance-matrix th,
        .attendance-matrix td {
            padding: 4px 2px;
        }
        .employee-info-cell strong {
            font-size: 11px;
        }
        .status-badge {
            padding: 2px 4px;
            font-size: 9px;
            min-width: 25px;
        }
        .progress {
            width: 30px;
        }
    }
</style>
@endsection

@php
    $leaveTypes = $leaveTypes ?? [];
@endphp

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="mb-1"><i class="fas fa-table me-2"></i> Detailed Attendance Report</h4>
                <small class="text-muted">Month: {{ $month }} (Click on any colored cell to edit attendance)</small>
            </div>
            <div>
                <a href="{{ route('admin.hrm.attendance.report') }}" class="btn btn-info shadow-sm px-4 me-2 mb-1">
                    <i class="fas fa-chart-line me-2"></i> Summary
                </a>
                <a href="{{ route('admin.hrm.attendance.index') }}" class="btn btn-primary shadow-sm px-4 mb-1">
                    <i class="fas fa-arrow-left me-2"></i> Daily
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month</label>
                    <input type="month" id="report_month" class="form-control" value="{{ $month }}" max="{{ date('Y-m') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadReportBtn" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Load
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <div class="export-buttons">
                        <button type="button" id="exportExcelBtn" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        {{-- <button type="button" id="printReportBtn" class="btn btn-secondary btn-sm">
                            <i class="fas fa-print me-1"></i> Print
                        </button> --}}
                    </div>
                </div>
            </div>

            <div class="matrix-scroll-wrapper" id="reportContainer">
                @include('admin.hrm.attendance.partials.matrix-table', compact('matrixData', 'dates', 'month'))
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Attendance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_employee_id">
                <input type="hidden" id="edit_date">

                <div class="mb-3">
                    <label class="form-label fw-bold">Employee</label>
                    <p id="edit_employee_name" class="fw-bold mb-0 p-2 bg-light rounded"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Date</label>
                    <p id="edit_date_display" class="mb-0 p-2 bg-light rounded"></p>
                </div>

                <div class="mb-3" id="leave_warning" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        This employee is on approved leave on this date!
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Status</label>
                    <div class="d-flex" style="gap: 12px">
                        <div class="form-check d-flex align-items-center">
                            <input type="radio" name="edit_status_radio" id="edit_status_present" value="present" class="big-radio form-check-input" style="width: 18px; height: 18px; margin-top: 0;">
                            <label class="form-check-label ms-2" style="cursor: pointer;">✅ Present</label>
                        </div>
                        <div class="form-check d-flex align-items-center">
                            <input type="radio" name="edit_status_radio" id="edit_status_absent" value="absent" class="big-radio form-check-input" style="width: 18px; height: 18px; margin-top: 0;">
                            <label class="form-check-label ms-2" style="cursor: pointer;">❌ Absent</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="edit_time_fields">
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="form-label">⏰ Check In</label>
                            <input type="time" id="edit_check_in" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">⏰ Check Out</label>
                            <input type="time" id="edit_check_out" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">📝 Remarks</label>
                    <textarea id="edit_remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveEditAttendanceBtn" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#attendance-details-report-sidebar').addClass('active');

    let leaveTypes = @json($leaveTypes ?? []);
    let globalDefaultCheckIn = '{{ $defaultCheckIn ?? "09:00" }}'.substring(0, 5);
    let globalDefaultCheckOut = '{{ $defaultCheckOut ?? "17:00" }}'.substring(0, 5);

    function loadReport() {
        var month = $('#report_month').val();
        var today = new Date().toISOString().split('T')[0];

        if (month > today.substring(0, 7)) {
            Swal.fire('Warning', 'Cannot view future months!', 'warning');
            $('#report_month').val(today.substring(0, 7));
            return;
        }

        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.attendance.details-report') }}",
            type: 'GET',
            data: { month: month },
            success: function(response) {
                Swal.close();
                $('#reportContainer').html($(response).find('#reportContainer').html());
            },
            error: function() {
                Swal.close();
                Swal.fire('Error', 'Failed to load report', 'error');
            }
        });
    }
    $('#editAttendanceModal').on('hidden.bs.modal', function() {
        $('#lateEarlyInfo').remove();
    });
// Edit cell click handler
$(document).on('click', '.attendance-matrix td[style*="cursor: pointer"], .attendance-matrix td[data-employee-id]', function() {
    var employeeId = $(this).data('employee-id');
    var employeeName = $(this).data('employee-name');
    var date = $(this).data('date');
    var currentStatus = $(this).data('status');
    var checkIn = $(this).data('check-in') || globalDefaultCheckIn || '09:00';
    var checkOut = $(this).data('check-out') || globalDefaultCheckOut || '17:00';
    var remarks = $(this).data('remarks') || '';
    var isWeekend = $(this).data('is-weekend') === true;
    var lateMinutes = $(this).data('late-minutes') || 0;
    var earlyMinutes = $(this).data('early-minutes') || 0;
    var lateFormatted = $(this).data('late-formatted') || '';
    var earlyFormatted = $(this).data('early-formatted') || '';

    // Check if date is future
    var today = new Date().toISOString().split('T')[0];
    if (date > today) {
        Swal.fire('Warning', 'Cannot edit future dates!', 'warning');
        return;
    }
    if (isWeekend) {
        Swal.fire({
            icon: 'warning',
            title: 'Weekend!',
            text: 'Cannot edit attendance for weekend dates!',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    $('#edit_employee_id').val(employeeId);
    $('#edit_employee_name').text(employeeName);
    $('#edit_date').val(date);
    $('#edit_date_display').text(date);
    $('#edit_remarks').val(remarks);

    $('#lateEarlyInfo').remove();
    // Show late/early info in modal
    var lateEarlyHtml = '';
    if (lateMinutes > 0) {
        lateEarlyHtml += '<div class="alert alert-warning py-1 mb-2">⏰ Late by: <strong>' + lateFormatted + '</strong></div>';
    }
    if (earlyMinutes > 0) {
        lateEarlyHtml += '<div class="alert alert-info py-1 mb-2">🏃 Early departure by: <strong>' + earlyFormatted + '</strong></div>';
    }
    if (lateEarlyHtml) {
        $('#editAttendanceModal .modal-body .mb-3:first').before('<div id="lateEarlyInfo">' + lateEarlyHtml + '</div>');
    } else {
        $('#lateEarlyInfo').remove();
    }

    // Default: Present checked, Absent unchecked
    if (currentStatus === 'present') {
        $('#edit_status_present').prop('checked', true);
        $('#edit_status_absent').prop('checked', false);
        $('#edit_time_fields').show();
        $('#edit_check_in').val(checkIn);
        $('#edit_check_out').val(checkOut);
    } else if (currentStatus === 'absent') {
        $('#edit_status_present').prop('checked', false);
        $('#edit_status_absent').prop('checked', true);
        $('#edit_time_fields').hide();
    } else {
        // For 'not_recorded' or any other status, default to Present
        $('#edit_status_present').prop('checked', true);
        $('#edit_status_absent').prop('checked', false);
        $('#edit_time_fields').show();
        $('#edit_check_in').val(globalDefaultCheckIn || '09:00');
        $('#edit_check_out').val(globalDefaultCheckOut || '17:00');
    }

    // Bind radio change events
    $('input[name="edit_status_radio"]').off('change').on('change', function() {
        if ($(this).val() === 'present') {
            $('#edit_time_fields').show();
        } else {
            $('#edit_time_fields').hide();
        }
    });

    $('#editAttendanceModal').modal('show');
});

// Save edit
$('#saveEditAttendanceBtn').click(function() {
    var employeeId = $('#edit_employee_id').val();
    var date = $('#edit_date').val();
    var status = $('input[name="edit_status_radio"]:checked').val();
    var remarks = $('#edit_remarks').val();

    if (!status) {
        Swal.fire('Error', 'Please select a status', 'error');
        return;
    }

    var postData = {
        _token: "{{ csrf_token() }}",
        employee_id: employeeId,
        date: date,
        status: status,
        remarks: remarks
    };

    if (status === 'present') {
        postData.check_in = $('#edit_check_in').val();
        postData.check_out = $('#edit_check_out').val();
    }

    Swal.fire({
        title: 'Saving...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "{{ route('admin.hrm.attendance.update-single') }}",
        type: 'POST',
        data: postData,
        success: function(response) {
            Swal.close();
            $('#editAttendanceModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });
            loadReport();
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', xhr.responseJSON?.error || 'Failed to update', 'error');
        }
    });
});

    $('#loadReportBtn').click(function() {
        loadReport();
    });

    $('#exportExcelBtn').click(function() {
        var table = document.querySelector('#reportContainer table');
        if (table) {
            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.table_to_sheet(table);
            XLSX.utils.book_append_sheet(wb, ws, 'Attendance_Report');
            XLSX.writeFile(wb, 'attendance_report_{{ $month }}.xlsx');
        } else {
            Swal.fire('Error', 'No data to export', 'error');
        }
    });

    // $('#printReportBtn').click(function() {
    //     var printContent = document.getElementById('reportContainer').innerHTML;
    //     var originalTitle = document.title;
    //     document.title = 'Attendance Report - {{ $month }}';
    //     var printWindow = window.open('', '_blank');
    //     printWindow.document.write(`
    //         <html>
    //             <head>
    //                 <title>Attendance Report - {{ $month }}</title>
    //                 <style>
    //                     body { font-family: Arial, sans-serif; padding: 20px; }
    //                     table { border-collapse: collapse; width: 100%; }
    //                     th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    //                     th { background-color: #f2f2f2; }
    //                     .present-cell { background-color: #d4edda; }
    //                     .absent-cell { background-color: #f8d7da; }
    //                     .not-recorded-cell { background-color: #fff3cd; }
    //                     .leave-cell { background-color: #cce5ff; }
    //                 </style>
    //             </head>
    //             <body>
    //                 <h2>Attendance Report - {{ $month }}</h2>
    //                 ${printContent}
    //             </body>
    //         </html>
    //     `);
    //     printWindow.document.close();
    //     printWindow.print();
    //     printWindow.close();
    //     document.title = originalTitle;
    // });

    // $('#report_month').on('change', function() {
    //     loadReport();
    // });
</script>
@endsection
