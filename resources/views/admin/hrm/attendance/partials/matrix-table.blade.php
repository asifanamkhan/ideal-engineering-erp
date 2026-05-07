{{-- resources/views/admin/hrm/attendance/partials/matrix-table.blade.php --}}

@php
    $leaveTypes = DB::table('leave_types')->where('status', 1)->get();
    $currentDate = date('Y-m-d');
    $weekends = DB::table('weekend_settings')->where('status', 1)->pluck('day_number')->toArray();
@endphp

<div id="attendanceNotice" class="alert alert-warning" style="">
    <i class="fas fa-info-circle me-2"></i>
    <span id="noticeMessage">You can update attendance by clicking on the day cell</span>
</div>

<!-- Legend -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex flex-wrap  p-2 bg-light rounded border" style="gap:10px;">
            <div><span class="badge" style="background-color: #28a745; color: white;">P</span> = Present (On Time)</div>
            <div><span class="badge" style="background-color: #ffc107; color: #212529;">PL</span> = Present Late</div>
            <div><span class="badge" style="background-color: #fd7e14; color: white;">PE</span> = Present Early Departure</div>
            <div><span class="badge" style="background-color: #17a2b8; color: white;">PLE</span> = Present Late & Early</div>
            <div><span class="badge" style="background-color: #dc3545; color: white;">A</span> = Absent</div>
            <div><span class="badge" style="background-color: #0066CC; color: white;">L</span> = On Leave</div>
            <div><span class="badge" style="background-color: #6c757d; color: white;">NR</span> = Not Recorded</div>
            <div><span class="badge" style="background-color: #17a2b8; color: white;">W</span> = Weekend</div>
        </div>
    </div>
</div>

<table class="table table-sm table-bordered attendance-matrix">
    <thead>
        <tr>
            <th rowspan="2" style="vertical-align: middle; position: sticky; left: 0; background: #28A745; color: white; z-index: 15;">#</th>
            <th rowspan="2" style="vertical-align: middle; position: sticky; left: 40px; background: #28A745; color: white; z-index: 15;">Employee</th>
            @foreach($dates as $date)
                <th style="text-align: center; min-width: 60px; background: #228B22; color: white;">
                    {{ $date['day'] }}<br>
                    <small>{{ $date['day_name'] }}</small>
                    <br><small style="font-size: 9px;">{{ $date['date'] }}</small>
                </th>
            @endforeach
            <th rowspan="2" style="vertical-align: middle; background: #228B22; color: white;">Present</th>
            <th rowspan="2" style="vertical-align: middle; background: #228B22; color: white;">Absent</th>
            <th rowspan="2" style="vertical-align: middle; background: #228B22; color: white;">Total</th>
            <th rowspan="2" style="vertical-align: middle; background: #228B22; color: white;">%</th>
        </tr>
    </thead>
    <tbody>
        @foreach($matrixData as $index => $row)
            @php
                // Calculate working days count (excluding weekends)
                $workingDaysCount = 0;
                $presentCount = 0;
                $absentCount = 0;

                foreach($row['attendance'] as $att) {
                    $isWeekend = $att['is_weekend'] ?? false;
                    if (!$isWeekend) {
                        $workingDaysCount++;
                        if ($att['status'] == 'present') {
                            $presentCount++;
                        } elseif ($att['status'] == 'absent') {
                            $absentCount++;
                        }
                    }
                }

                $attendancePercentage = $workingDaysCount > 0 ? round(($presentCount / $workingDaysCount) * 100, 2) : 0;
            @endphp
            <tr>
                <td style="position: sticky; left: 0; background: white; z-index: 5; text-align: center;">{{ $index + 1 }}</td>
                <td style="position: sticky; left: 40px; background: white; z-index: 5; text-align: left;">
                    <div class="employee-info-cell">
                        <strong>{{ $row['name'] }}</strong><br>
                        <small class="text-muted">ID: {{ $row['employee_id'] }}</small><br>
                        <small class="text-muted">{{ $row['designation'] }}</small>
                    </div>
                </td>

                @foreach($row['attendance'] as $attIndex => $att)
                    @php
                        $bgColor = '';
                        $badgeColor = '';
                        $badgeTextColor = 'white';
                        $statusText = '';
                        $onLeave = false;
                        $tooltipTitle = '';

                        // Check if date is future
                        $isFuture = $att['date'] > $currentDate;

                        // Check if date is weekend
                        $dayOfWeek = \Carbon\Carbon::parse($att['date'])->dayOfWeek;
                        $isWeekend = in_array($dayOfWeek, $weekends);

                        // Get late/early minutes if available
                        $lateMinutes = $att['late_minutes'] ?? 0;
                        $earlyMinutes = $att['early_minutes'] ?? 0;

                        // Convert minutes to hours and minutes format
                        $lateFormatted = '';
                        $earlyFormatted = '';

                        if ($lateMinutes > 0) {
                            $lateHours = floor($lateMinutes / 60);
                            $lateRemainingMinutes = $lateMinutes % 60;
                            if ($lateHours > 0) {
                                $lateFormatted = $lateHours . 'h ' . $lateRemainingMinutes . 'm';
                            } else {
                                $lateFormatted = $lateMinutes . 'm';
                            }
                        }

                        if ($earlyMinutes > 0) {
                            $earlyHours = floor($earlyMinutes / 60);
                            $earlyRemainingMinutes = $earlyMinutes % 60;
                            if ($earlyHours > 0) {
                                $earlyFormatted = $earlyHours . 'h ' . $earlyRemainingMinutes . 'm';
                            } else {
                                $earlyFormatted = $earlyMinutes . 'm';
                            }
                        }

                        if ($isWeekend) {
                            $bgColor = '#c2e8cb';  // Light green background
                            $badgeColor = 'none';  // Teal/Blue for weekend
                            $badgeTextColor = 'black !important';
                            $statusText = 'W';
                            $tooltipTitle = 'Weekend';
                        } elseif ($att['status'] == 'present') {
                            if ($lateMinutes > 0 && $earlyMinutes > 0) {
                                $bgColor = '#D1ECF1';  // Light red background
                                $badgeColor = '#17A2B8';  // Dark red
                                $statusText = 'PLE';
                                $tooltipTitle = "Present but Late: {$lateFormatted} & Early: {$earlyFormatted}";
                            } elseif ($lateMinutes > 0) {
                                $bgColor = '#fff3cd';  // Light yellow background
                                $badgeColor = '#ffc107';  // Dark yellow
                                $badgeTextColor = '#212529';
                                $statusText = 'PL';
                                $tooltipTitle = "Present but Late by {$lateFormatted}";
                            } elseif ($earlyMinutes > 0) {
                                $bgColor = '#ffdfc5';  // Light cyan background
                                $badgeColor = '#fd7e14';  // Dark orange
                                $statusText = 'PE';
                                $tooltipTitle = "Left Early by {$earlyFormatted}";
                            } else {
                                $bgColor = '#d4edda';  // Light green background
                                $badgeColor = '#28a745';  // Dark green
                                $statusText = 'P';
                                $tooltipTitle = 'Present (On Time)';
                            }
                        } elseif ($att['status'] == 'absent') {
                            $bgColor = '#f8d7da';  // Light red background
                            $badgeColor = '#dc3545';  // Dark red
                            $statusText = 'A';
                            $tooltipTitle = 'Absent';
                        } elseif ($att['status'] == 'on_leave') {
                            $bgColor = '#cce5ff';  // Light blue background
                            $badgeColor = '#0066CC';  // Dark blue
                            $statusText = 'L';
                            $onLeave = true;
                            $tooltipTitle = 'On Approved Leave';
                        } else {
                            $bgColor = '#e9ecef';  // Light gray background
                            $badgeColor = '#6c757d';  // Dark gray
                            $statusText = 'NR';
                            $tooltipTitle = 'Not Recorded';
                        }

                        $canEdit = !$isFuture && !$onLeave && !$isWeekend;
                    @endphp
                    <td style="background-color: {{ $bgColor }}; text-align: center; vertical-align: middle; {{ !$canEdit ? 'cursor: not-allowed;' : 'cursor: pointer;' }}"
                        data-employee-id="{{ $row['id'] }}"
                        data-employee-name="{{ $row['name'] }}"
                        data-date="{{ $att['date'] }}"
                        data-status="{{ $att['status'] }}"
                        data-check-in="{{ $att['check_in'] ?? '09:00' }}"
                        data-check-out="{{ $att['check_out'] ?? '17:00' }}"
                        data-leave-type="{{ $att['leave_type_id'] ?? '' }}"
                        data-remarks="{{ $att['remarks'] ?? '' }}"
                        data-on-leave="{{ $onLeave ? 'true' : 'false' }}"
                        data-is-weekend="{{ $isWeekend ? 'true' : 'false' }}"
                        data-late-minutes="{{ $lateMinutes }}"
                        data-early-minutes="{{ $earlyMinutes }}"
                        data-late-formatted="{{ $lateFormatted }}"
                        data-early-formatted="{{ $earlyFormatted }}"
                        title="{{ $tooltipTitle }}">
                        <span class="badge" style="background-color: {{ $badgeColor }}; color: {{ $badgeTextColor }}; font-size: 12px; padding: 4px 10px;">{{ $statusText }}</span>
                        @if($isWeekend)
                            <div class="small text-muted" style="font-size: 11px;">Weekend</div>
                        @endif
                    </td>
                @endforeach

                <td class="text-center" style="background-color: #d4edda;"><strong>{{ $presentCount }}</strong></td>
                <td class="text-center" style="background-color: #f8d7da;"><strong>{{ $absentCount }}</strong></td>
                <td class="text-center"><strong>{{ $workingDaysCount }}</strong></td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="progress" style="height: 4px; width: 50px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendancePercentage }}%"></div>
                        </div>
                        <span class="ms-1">{{ $attendancePercentage }}%</span>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot class="summary-row" style="background-color: #f8f9fc;">
        <td colspan="2" class="text-end fw-bold"><strong>Summary:</strong></td>
        @php
            $totalPresent = 0;
            $totalAbsent = 0;
            $totalWorkingDays = 0;

            foreach($matrixData as $row) {
                foreach($row['attendance'] as $att) {
                    $isWeekend = $att['is_weekend'] ?? false;
                    if (!$isWeekend) {
                        $totalWorkingDays++;
                        if ($att['status'] == 'present') {
                            $totalPresent++;
                        } elseif ($att['status'] == 'absent') {
                            $totalAbsent++;
                        }
                    }
                }
            }
        @endphp
        @foreach($dates as $index => $date)
            @php
                $dayPresent = 0;
                $isDateWeekend = in_array(\Carbon\Carbon::parse($date['date'])->dayOfWeek, $weekends);
                if (!$isDateWeekend) {
                    foreach($matrixData as $row) {
                        if (isset($row['attendance'][$index]['status']) && $row['attendance'][$index]['status'] == 'present') {
                            $dayPresent++;
                        }
                    }
                }
            @endphp
            <td class="text-center" style="background-color: #d4edda;">
                @if(!$isDateWeekend)
                    <small>P: {{ $dayPresent }}</small>
                @else
                    <small>W</small>
                @endif
            </td>
        @endforeach
        <td class="text-center fw-bold" style="background-color: #d4edda;"><strong>{{ $totalPresent }}</strong></td>
        <td class="text-center fw-bold" style="background-color: #f8d7da;"><strong>{{ $totalAbsent }}</strong></td>
        <td class="text-center fw-bold"><strong>{{ $totalWorkingDays }}</strong></td>
        <td class="text-center fw-bold">
            @php
                $totalPercentage = $totalWorkingDays > 0 ? round(($totalPresent / $totalWorkingDays) * 100, 2) : 0;
            @endphp
            <strong>{{ $totalPercentage }}%</strong>
        </td>
    </tfoot>
</table>
