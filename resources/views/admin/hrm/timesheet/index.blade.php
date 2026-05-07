{{-- resources/views/admin/hrm/timesheet/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>


    .timesheet-table th {
        background-color: #228B22;
        color: white;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
    }

    .timesheet-table td {
        vertical-align: middle;
        text-align: center;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        min-width: 40px;
        font-size: 12px;
    }

    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 15px;
        color: white;
        margin-bottom: 20px;
    }

    .summary-card h6 {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        opacity: 0.8;
    }

    .summary-card h3 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 0;
    }

    .weekend-row {
        background-color: #c2e8cb;
    }

    .leave-row {
        background-color: #cce5ff;
    }

    .export-btn {
        margin-right: 10px;
    }



    .timesheet-table {
        min-width: 900px;
    }

    /* Simple employee card - no background */
    .simple-employee-card {
        padding: 15px 0;
        border-bottom: 1px solid #e3e6f0;
        margin-bottom: 20px;
    }

    .job-tag {
        display: inline-block;
        background: #eef2ff;
        color: #228B22;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin: 2px;
        white-space: nowrap;
    }

    .positive {
        color: #28a745;
        font-weight: 600;
    }

    .negative {
        color: #dc3545;
        font-weight: 600;
    }

    .hours-cell {
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-calendar-alt me-2"></i> Timesheet</h4>
            </div>
            <div>
                <a href="{{ route('admin.hrm.attendance.index') }}" class="btn btn-primary shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Back to Attendance
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Select Employee <span class="text-danger">*</span></label>
                    <select id="employee_id" class="form-control select2-employee" style="width: 100%;">
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month</label>
                    <input type="month" id="month_year" class="form-control" value="{{ $currentMonth }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadTimesheetBtn" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Load
                    </button>
                </div>
                <div class="col-md-2 text-end">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <div>
                        <button type="button" id="exportExcelBtn" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button type="button" id="printBtn" class="btn btn-secondary btn-sm">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Employee Info Card (no background) -->
    <div id="employeeInfoCard" style="display: none;">
        <div class="simple-employee-card">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="mb-0" id="emp_name"></h5>
                        <small class="text-muted" id="emp_id"></small><br>
                        <small class="text-muted" id="emp_designation"></small>
                    </div>
                </div>
                <div>
                    <h5 id="month_display" class="mb-0 text-primary"></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row" id="summaryCards" style="display: none;">
        <div class="col-md-2">
            <div class="summary-card">
                <h6>Present Days</h6>
                <h3 id="summary_present">0</h3>
                <small id="summary_percentage">0%</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <h6>Late</h6>
                <h3 id="summary_late">0</h3>
                <small id="summary_late_detail">0 min</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-card" style="background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);">
                <h6>Early</h6>
                <h3 id="summary_early">0</h3>
                <small id="summary_early_detail">0 min</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <h6>Overtime</h6>
                <h3 id="summary_overtime">0</h3>
                <small>hours</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-card" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <h6>Absent Days</h6>
                <h3 id="summary_absent">0</h3>
                <small>days</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-card" style="background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);">
                <h6>Avg Effective Hrs</h6>
                <h3 id="summary_avg_hours">0</h3>
                <small>hours/day</small>
            </div>
        </div>
    </div>

    <!-- Timesheet Table -->
    <div class="card shadow mb-4" id="timesheetCard" style="display: none;">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-table me-2"></i> Daily Timesheet</h6>
        </div>
        <div class="card-body">
            <div class="scroll-wrapper">
                <table class="table table-sm table-bordered timesheet-table" id="timesheet-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Net Hours</th>
                            <th>Late</th>
                            <th>Early</th>
                            <th>OT</th>
                            <th>Effective Hrs</th>
                            <th>Projects</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="timesheet-tbody">
                        <tr>
                            <td colspan="13" class="text-center text-muted">Select employee and month, then click Load
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="timesheet-tfoot" style="display: none;">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total / Average:</td>
                            <td class="text-end fw-bold" id="total_net_hours">0.00h</td>
                            <td class="text-end fw-bold" id="total_late">0</td>
                            <td class="text-end fw-bold" id="total_early">0</td>
                            <td class="text-end fw-bold" id="total_overtime">0.00h</td>
                            <td class="text-end fw-bold" id="total_effective_hours">0.00h</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#timesheet-sidebar').addClass('active');

    let currentTimesheetData = [];

    $('.select2-employee').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search employee...',
        width: '100%'
    });

    function formatMinutes(totalMinutes) {
        if (!totalMinutes || totalMinutes === 0) return '0m';
        let hours = Math.floor(Math.abs(totalMinutes) / 60);
        let minutes = Math.abs(totalMinutes) % 60;
        let sign = totalMinutes < 0 ? '-' : '';
        if (hours > 0 && minutes > 0) return sign + hours + 'h ' + minutes + 'm';
        if (hours > 0) return sign + hours + 'h';
        return sign + minutes + 'm';
    }

    function formatHoursToHoursMinutes(totalHours) {
        if (!totalHours || totalHours === 0) return '0h';
        let hours = Math.floor(totalHours);
        let minutes = Math.round((totalHours - hours) * 60);
        if (minutes === 60) {
            hours++;
            minutes = 0;
        }
        if (hours > 0 && minutes > 0) return hours + 'h ' + minutes + 'm';
        if (hours > 0) return hours + 'h';
        return minutes + 'm';
    }

    function getStatusStyle(status) {
        const styles = {
            'P': { bg: '#28a745', color: 'white', text: 'P' },
            'PL': { bg: '#ffc107', color: '#212529', text: 'PL' },
            'PE': { bg: '#fd7e14', color: 'white', text: 'PE' },
            'PLE': { bg: '#dc3545', color: 'white', text: 'PLE' },
            'A': { bg: '#dc3545', color: 'white', text: 'A' },
            'L': { bg: '#0066CC', color: 'white', text: 'L' },
            'W': { bg: '#17a2b8', color: 'white', text: 'W' },
            'NR': { bg: '#6c757d', color: 'white', text: 'NR' }
        };
        return styles[status] || styles['NR'];
    }

    $('#loadTimesheetBtn').click(function() {
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
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.timesheet.get-data') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                employee_id: employeeId,
                month_year: monthYear
            },
            success: function(response) {
                Swal.close();
                currentTimesheetData = response.timesheet_data;

                $('#employeeInfoCard').show();
                $('#summaryCards').show();
                $('#timesheetCard').show();

                $('#emp_name').text(response.employee.name);
                $('#emp_id').text('ID: ' + response.employee.employee_id);
                $('#emp_designation').text(response.employee.designation || 'N/A');
                $('#month_display').text(response.month_name);

                // Set summary
                $('#summary_present').text(response.summary.total_present);
                $('#summary_absent').text(response.summary.total_absent);
                $('#summary_late').text(response.summary.total_late_minutes > 0 ? response.summary.total_late_minutes : '0');
                $('#summary_early').text(response.summary.total_early_minutes > 0 ? response.summary.total_early_minutes : '0');
                $('#summary_overtime').text(response.summary.total_overtime_hours.toFixed(1));
                $('#summary_avg_hours').text(formatHoursToHoursMinutes(response.summary.avg_effective_hours));
                $('#summary_percentage').text(response.summary.attendance_percentage + '%');
                $('#summary_late_detail').text(formatMinutes(response.summary.total_late_minutes));
                $('#summary_early_detail').text(formatMinutes(response.summary.total_early_minutes));

                renderTimesheetTable();
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load data', 'error');
            }
        });
    });

    function renderTimesheetTable() {
        var tbody = $('#timesheet-tbody');
        tbody.empty();

        if (!currentTimesheetData || currentTimesheetData.length === 0) {
            tbody.append('<td><td colspan="13" class="text-center text-muted">No data found</td></tr>');
            $('#timesheet-tfoot').hide();
            return;
        }

        var totalNetHours = 0;
        var totalEffectiveHours = 0;
        var totalLate = 0;
        var totalEarly = 0;
        var totalOvertime = 0;

        $.each(currentTimesheetData, function(index, item) {
            var rowClass = '';
            if (item.is_weekend) rowClass = 'weekend-row';
            else if (item.on_leave) rowClass = 'leave-row';

            var statusStyle = getStatusStyle(item.status);

            var netWorkingHours = parseFloat(item.net_working_hours) || 0;
            if (netWorkingHours < 0) netWorkingHours = 0;

            var effectiveHours = parseFloat(item.effective_hours) || 0;
            if (effectiveHours < 0) effectiveHours = 0;

            var lateMinutes = parseFloat(item.late_minutes) || 0;
            if (lateMinutes < 0) lateMinutes = 0;

            var earlyMinutes = parseFloat(item.early_minutes) || 0;
            if (earlyMinutes < 0) earlyMinutes = 0;

            var overtimeHours = parseFloat(item.overtime_hours) || 0;
            if (overtimeHours < 0) overtimeHours = 0;

            var jobsHtml = '-';
            if (item.jobs && item.jobs.length > 0) {
                jobsHtml = '';
                $.each(item.jobs, function(jIdx, job) {
                    jobsHtml += '<span class="job-tag" title="Job #' + job.job_no + '">' + job.title + '</span> ';
                });
            }

            // Hours ফরম্যাট করা (21.18h -> 21h 11m)
            var netHoursDisplay = netWorkingHours > 0 ? formatHoursToHoursMinutes(netWorkingHours) : '-';
            var effectiveHoursDisplay = effectiveHours > 0 ? formatHoursToHoursMinutes(effectiveHours) : '-';
            var lateDisplay = lateMinutes > 0 ? formatMinutes(lateMinutes) : '-';
            var earlyDisplay = earlyMinutes > 0 ? formatMinutes(earlyMinutes) : '-';
            var overtimeDisplay = overtimeHours > 0 ? overtimeHours.toFixed(1) + 'h' : '-';

            if (!item.is_weekend && !item.on_leave && item.status !== 'A' && item.status !== 'NR') {
                totalNetHours += netWorkingHours;
                totalEffectiveHours += effectiveHours;
            }
            totalLate += lateMinutes;
            totalEarly += earlyMinutes;
            totalOvertime += overtimeHours;

            var row = '<tr class="' + rowClass + '">' +
                '<td class="text-center">' + (index + 1) + '</td>' +
                '<td class="text-center">' + (item.date_formatted || item.date) + '</td>' +
                '<td class="text-center">' + (item.day_short || item.day_name) + '</td>' +
                '<td class="text-center"><span class="status-badge" style="background: ' + statusStyle.bg + '; color: ' + statusStyle.color + '">' + statusStyle.text + '</span></td>' +
                '<td class="text-center">' + (item.check_in || '-') + '</td>' +
                '<td class="text-center">' + (item.check_out || '-') + '</td>' +
                '<td class="text-center hours-cell">' + netHoursDisplay + '</td>' +
                '<td class="text-center ' + (lateMinutes > 0 ? 'negative' : '') + '">' + lateDisplay + '</td>' +
                '<td class="text-center ' + (earlyMinutes > 0 ? 'negative' : '') + '">' + earlyDisplay + '</td>' +
                '<td class="text-center ' + (overtimeHours > 0 ? 'positive' : '') + '">' + overtimeDisplay + '</td>' +
                '<td class="text-center hours-cell">' + effectiveHoursDisplay + '</td>' +
                '<td class="text-center">' + jobsHtml + '</td>' +
                '<td class="text-start small">' + (item.remarks || '-') + '</td>' +
                '</tr>';
            tbody.append(row);
        });

        // Footer এ Total দেখানো (Hours/Minutes ফরম্যাটে)
        $('#total_net_hours').text(formatHoursToHoursMinutes(totalNetHours));
        $('#total_effective_hours').text(formatHoursToHoursMinutes(totalEffectiveHours));
        $('#total_late').text(formatMinutes(totalLate));
        $('#total_early').text(formatMinutes(totalEarly));
        $('#total_overtime').text(totalOvertime.toFixed(1) + 'h');
        $('#timesheet-tfoot').show();
    }

    $('#exportExcelBtn').click(function() {
        var employeeName = $('#emp_name').text();
        var month = $('#month_display').text();

        var wsData = [
            ['Timesheet Report'],
            ['Employee:', employeeName],
            ['Month:', month],
            [],
            ['#', 'Date', 'Day', 'Status', 'Check In', 'Check Out', 'Net Hours', 'Late', 'Early', 'OT', 'Effective Hours', 'Remarks']
        ];

        $.each(currentTimesheetData, function(index, item) {
            var netWorkingHours = Math.max(0, item.net_working_hours || 0);
            var effectiveHours = Math.max(0, item.effective_hours || 0);
            var lateMinutes = Math.max(0, item.late_minutes || 0);
            var earlyMinutes = Math.max(0, item.early_minutes || 0);
            var overtimeHours = Math.max(0, item.overtime_hours || 0);

            wsData.push([
                index + 1,
                item.date_formatted || item.date,
                item.day_name,
                getStatusStyle(item.status).text,
                item.check_in || '-',
                item.check_out || '-',
                formatHoursToHoursMinutes(netWorkingHours),
                formatMinutes(lateMinutes),
                formatMinutes(earlyMinutes),
                (overtimeHours > 0 ? overtimeHours.toFixed(1) + 'h' : '-'),
                formatHoursToHoursMinutes(effectiveHours),
                item.remarks || '-'
            ]);
        });

        var ws = XLSX.utils.aoa_to_sheet(wsData);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Timesheet');
        XLSX.writeFile(wb, 'timesheet_' + employeeName + '_' + month + '.xlsx');
    });

    $('#printBtn').click(function() {
        window.print();
    });
</script>
@endsection
