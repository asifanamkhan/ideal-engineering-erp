<?php
// app/Http/Controllers/Admin/Hrm/TimesheetController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $employees = DB::table('employees')
            ->where('status', 1)
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->get();

        $currentMonth = Carbon::now('Asia/Dhaka')->format('Y-m');

        return view('admin.hrm.timesheet.index', compact('employees', 'currentMonth'));
    }

    public function getData(Request $request)
    {
        $employeeId = $request->employee_id;
        $monthYear = $request->month_year;

        $year = substr($monthYear, 0, 4);
        $month = substr($monthYear, 5, 2);
        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get employee details
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->select('id', 'name', 'employee_id', 'designation_id')
            ->first();

        // Get designation
        $designation = DB::table('designations')->where('id', $employee->designation_id)->value('name');
        $employee->designation = $designation ?? 'N/A';

        // Get weekend settings
        $weekends = DB::table('weekend_settings')
            ->where('status', 1)
            ->pluck('day_number')
            ->toArray();

        // Get all dates in the month
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $today = Carbon::now('Asia/Dhaka')->startOfDay();
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i);

            // future date skip করবে
            if ($date->startOfDay() > $today) {
                break;
            }

            $dates[] = [
                'day' => $i,
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('d M, Y'),
                'day_name' => $date->format('l'),
                'day_short' => $date->format('D'),
                'is_weekend' => in_array($date->dayOfWeek, $weekends)
            ];
        }

        // Get attendance records
        $attendances = DB::table('attendance_records')
            ->where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get()
            ->keyBy('attendance_date');

        // Get leave records
        $leaves = DB::table('leave_applications')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->get();

        // Get overtime records
        $overtimes = DB::table('overtime_records')
            ->where('employee_id', $employeeId)
            ->whereYear('overtime_date', $year)
            ->whereMonth('overtime_date', $month)
            ->get()
            ->keyBy('overtime_date');

        // Get job_books for this employee
        $jobBooks = DB::table('job_books')
            ->where(function($q) use ($employeeId) {
                $q->where('assign_to', 'LIKE', '%,' . $employeeId . ',%')
                  ->orWhere('assign_to', 'LIKE', $employeeId . ',%')
                  ->orWhere('assign_to', 'LIKE', '%,' . $employeeId)
                  ->orWhere('assign_to', (string)$employeeId);
            })
            ->whereBetween('job_date', [$startDate, $endDate])
            ->orderBy('job_date')
            ->get();

        // Group jobs by date
        $jobsByDate = [];
        foreach ($jobBooks as $job) {
            $date = $job->job_date;
            if (!isset($jobsByDate[$date])) {
                $jobsByDate[$date] = [];
            }
            $jobsByDate[$date][] = [
                'id' => $job->id,
                'title' => $job->title ?? 'Job #' . $job->id,
                'job_no' => $job->job_no ?? 'N/A',
                'delivery_date' => $job->delivery_date
            ];
        }

        // Prepare timesheet data
        $timesheetData = [];
        $summary = [
            'total_present' => 0,
            'total_absent' => 0,
            'total_late_minutes' => 0,
            'total_early_minutes' => 0,
            'total_overtime_hours' => 0,
            'total_net_hours' => 0,
            'total_effective_hours' => 0,
            'total_working_days' => 0
        ];

        foreach ($dates as $date) {
            $attendance = $attendances[$date['date']] ?? null;
            $overtime = $overtimes[$date['date']] ?? null;
            $jobsOnDate = $jobsByDate[$date['date']] ?? [];

            // Check if on leave
            $onLeave = false;
            $leaveType = null;
            foreach ($leaves as $leave) {
                if ($date['date'] >= $leave->start_date && $date['date'] <= $leave->end_date) {
                    $onLeave = true;
                    $leaveType = $leave->leave_type ?? 'Leave';
                    break;
                }
            }

            $status = 'NR';
            $lateMinutes = 0;
            $earlyMinutes = 0;
            $checkIn = null;
            $checkOut = null;
            $remarks = null;
            $overtimeHours = $overtime->overtime_hours ?? 0;
            $overtimeHours = is_numeric($overtimeHours) ? floatval($overtimeHours) : 0;
            $overtimeHours = max(0, $overtimeHours);

            // Calculate working hours
            $netWorkingHours = 0;
            $effectiveHours = 0;

            if ($date['is_weekend']) {
                $status = 'W';
                $remarks = 'Weekend Off';
            } elseif ($onLeave) {
                $status = 'L';
                $remarks = $leaveType;
            } elseif ($attendance) {
                \Log::info('Check In Raw: ' . $attendance->check_in);
                \Log::info('Check Out Raw: ' . $attendance->check_out);
                $checkInTime = $attendance->check_in ? Carbon::createFromFormat('H:i:s', $attendance->check_in) : null;
                $checkOutTime = $attendance->check_out ? Carbon::createFromFormat('H:i:s', $attendance->check_out) : null;

                $checkIn = $checkInTime ? $checkInTime->format('h:i A') : null;
                $checkOut = $checkOutTime ? $checkOutTime->format('h:i A') : null;

                $lateMinutes = max(0, $attendance->late_minutes ?? 0);
                $earlyMinutes = max(0, $attendance->early_minutes ?? 0);
                $remarks = $attendance->remarks;

                if ($checkInTime && $checkOutTime) {
                    // শুধু সময় compare করার জন্য
                    $checkInSeconds = ($checkInTime->hour * 3600) + ($checkInTime->minute * 60);
                    $checkOutSeconds = ($checkOutTime->hour * 3600) + ($checkOutTime->minute * 60);

                    if ($checkOutSeconds > $checkInSeconds) {
                        $diffSeconds = $checkOutSeconds - $checkInSeconds;
                        $netWorkingHours = round($diffSeconds / 3600, 2);
                    } else {
                        $netWorkingHours = 0;
                    }
                } else {
                    $netWorkingHours = 0;
                }

                // Calculate Effective Hours = Net Hours + Overtime - (Late/60) - (Early/60)
                // Ensure it never goes negative
                $lateHours = round($lateMinutes / 60, 2);
                $earlyHours = round($earlyMinutes / 60, 2);
                $effectiveHours = $netWorkingHours + $overtimeHours - $lateHours - $earlyHours;
                $effectiveHours = max(0, round($effectiveHours, 2));

                // Determine status based on attendance
                if ($attendance->status == 'present') {
                    if ($lateMinutes > 0 && $earlyMinutes > 0) {
                        $status = 'PLE';
                    } elseif ($lateMinutes > 0) {
                        $status = 'PL';
                    } elseif ($earlyMinutes > 0) {
                        $status = 'PE';
                    } else {
                        $status = 'P';
                    }
                    $summary['total_present']++;
                    $summary['total_late_minutes'] += $lateMinutes;
                    $summary['total_early_minutes'] += $earlyMinutes;
                    $summary['total_net_hours'] += $netWorkingHours;
                    $summary['total_effective_hours'] += $effectiveHours;
                } elseif ($attendance->status == 'absent') {
                    $status = 'A';
                    $summary['total_absent']++;
                }
            } else {
                $status = 'NR';
            }

            if (!$date['is_weekend'] && !$onLeave && $status != 'A') {
                $summary['total_working_days']++;
            }

            $summary['total_overtime_hours'] += $overtimeHours;

            $timesheetData[] = [
                'date' => $date['date'],
                'date_formatted' => $date['date_formatted'],
                'day' => $date['day'],
                'day_name' => $date['day_name'],
                'day_short' => $date['day_short'],
                'is_weekend' => $date['is_weekend'],
                'status' => $status,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'net_working_hours' => $netWorkingHours,
                'effective_hours' => $effectiveHours,
                'late_minutes' => $lateMinutes,
                'early_minutes' => $earlyMinutes,
                'overtime_hours' => $overtimeHours,
                'remarks' => $remarks,
                'on_leave' => $onLeave,
                'jobs' => $jobsOnDate
            ];
        }

        // Calculate summary stats
        $summary['attendance_percentage'] = $summary['total_working_days'] > 0
            ? round(($summary['total_present'] / $summary['total_working_days']) * 100, 2)
            : 0;

        $summary['avg_net_hours'] = $summary['total_present'] > 0
            ? round($summary['total_net_hours'] / $summary['total_present'], 2)
            : 0;

        $summary['avg_effective_hours'] = $summary['total_present'] > 0
            ? round($summary['total_effective_hours'] / $summary['total_present'], 2)
            : 0;

        return response()->json([
            'employee' => $employee,
            'timesheet_data' => $timesheetData,
            'summary' => $summary,
            'month_year' => $monthYear,
            'month_name' => Carbon::create($year, $month, 1)->format('F Y')
        ]);
    }
}