
{{-- resources/views/admin/hrm/attendance/report.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .progress {
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-chart-line me-2"></i> Attendance Report</h4>
            </div>
            <div>
                <a href="{{ route('admin.hrm.attendance.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Daily Attendance
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month</label>
                    <input type="month" id="report_month" class="form-control" value="{{ $currentMonth }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadReportBtn" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Load Report
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="report-table" width="100%">
                    <thead class="table-head">
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Half Day</th>
                            <th>Total Days</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#attendance-report-sidebar').addClass('active');

    var table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.hrm.attendance.report') }}",
            data: function(d) {
                d.month = $('#report_month').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'name', name: 'name' },
            { data: 'designation', name: 'designation' },
            { data: 'present', name: 'present', className: 'text-center' },
            { data: 'absent', name: 'absent', className: 'text-center' },
            { data: 'late', name: 'late', className: 'text-center' },
            { data: 'half_day', name: 'half_day', className: 'text-center' },
            { data: 'total_days', name: 'total_days', className: 'text-center' },
            { data: 'attendance_percentage', name: 'attendance_percentage' }
        ],
        order: [[2, 'asc']],
        pageLength: 25
    });

    $('#loadReportBtn').click(function() {
        table.ajax.reload();
    });

    // Add this function to check weekend
function isWeekend(date) {
    var day = new Date(date).getDay();
    // 5 = Friday, 6 = Saturday (as per weekend_settings)
    return (day === 5 || day === 6);
}

// Update the date change event
$('#attendance_date').on('change', function() {
    var selectedDate = $(this).val();
    var today = new Date().toISOString().split('T')[0];

    if (selectedDate > today) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Date',
            text: 'You cannot select future dates!'
        });
        $(this).val(today);
        return;
    }

    // Check if selected date is weekend
    if (isWeekend(selectedDate)) {
        Swal.fire({
            icon: 'warning',
            title: 'Weekend!',
            text: 'Today is a weekend. You cannot add attendance. Overtime can be entered separately.',
            confirmButtonColor: '#3085d6'
        });
        $('#attendance-tbody').html('<tr><td colspan="7" class="text-center text-muted">Weekend - No attendance required. You can enter overtime from Overtime module.</td></tr>');
        $('#saveAttendanceBtn').prop('disabled', true);
        return;
    } else {
        $('#saveAttendanceBtn').prop('disabled', false);
        loadAttendance();
    }
});
</script>
@endsection
