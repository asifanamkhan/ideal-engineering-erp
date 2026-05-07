<?php
// app/Http/Controllers/Admin/Hrm/AttendanceController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?? Carbon::now('Asia/Dhaka')->toDateString();

        // Get HRM settings
        $settings = DB::table('hrm_settings')->first();
        $defaultCheckIn = $settings->default_check_in ?? '09:00:00';
        $defaultCheckOut = $settings->default_check_out ?? '17:00:00';

        // Check if date is future
        $isFutureDate = Carbon::parse($date)->isFuture();

        // Check if attendance already exists for this date
        $attendanceExists = DB::table('attendance_records')
            ->where('attendance_date', $date)
            ->exists();
        $lastUpdated = null;
        if ($attendanceExists) {
            $lastUpdatedRecord = DB::table('attendance_records')
                ->where('attendance_date', $date)
                ->orderBy('updated_at', 'desc')
                ->first();
            $lastUpdated = $lastUpdatedRecord ? Carbon::parse($lastUpdatedRecord->updated_at)->format('d-m-Y h:i A') : null;
        }

        if ($request->ajax()) {
            // Get employees with attendance for selected date
            $employees = DB::table('employees')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->leftJoin('attendance_records', function ($join) use ($date) {
                    $join->on('employees.id', '=', 'attendance_records.employee_id')
                        ->where('attendance_records.attendance_date', '=', $date);
                })
                ->leftJoin('leave_types', 'attendance_records.leave_type_id', '=', 'leave_types.id')
                ->where('employees.status', 1)
                ->whereNotExists(function ($query) use ($date) {
                    $query->select(DB::raw(1))
                        ->from('leave_applications')
                        ->whereRaw('leave_applications.employee_id = employees.id')
                        ->where('leave_applications.status', 'approved')
                        ->where('leave_applications.start_date', '<=', $date)
                        ->where('leave_applications.end_date', '>=', $date);
                })
                ->select(
                    'employees.id',
                    'employees.name',
                    'employees.employee_id',
                    'designations.name as designation',
                    'attendance_records.check_in',
                    'attendance_records.check_out',
                    'attendance_records.status',
                    'attendance_records.leave_type_id',
                    'attendance_records.remarks',
                    'attendance_records.late_minutes',
                    'attendance_records.early_minutes',
                    'attendance_records.total_late_early_minutes',
                    'leave_types.name as leave_type_name',
                    'leave_types.is_paid'
                )
                ->orderBy('employees.name')
                ->get();

            return response()->json([
                'employees' => $employees,
                'date' => $date,
                'is_future' => $isFutureDate,
                'attendance_exists' => $attendanceExists,
                'last_updated' => $lastUpdated,
                'default_check_in' => $defaultCheckIn,
                'default_check_out' => $defaultCheckOut
            ]);
        }

        $leaveTypes = DB::table('leave_types')->where('status', 1)->get();
        return view('admin.hrm.attendance.index', compact('date', 'leaveTypes', 'defaultCheckIn', 'defaultCheckOut'));
    }

    public function store(Request $request)
    {
        $date = $request->date;
        if (Carbon::parse($date)->isFuture()) {
            return response()->json(['error' => 'Cannot save attendance for future dates!'], 422);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.employee_id' => 'required|exists:employees,id',
            'attendance.*.status' => 'required|in:present,absent',
            'attendance.*.check_in' => 'nullable',
            'attendance.*.check_out' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->attendance as $data) {
                $existing = DB::table('attendance_records')
                    ->where('employee_id', $data['employee_id'])
                    ->where('attendance_date', $request->date)
                    ->first();

                $attendanceData = [
                    'employee_id' => $data['employee_id'],
                    'attendance_date' => $request->date,
                    'status' => $data['status'],
                    'remarks' => $data['remarks'] ?? null,
                    'updated_at' => now(),
                    'created_by' => auth()->id()
                ];

                if ($data['status'] == 'present') {
                    $checkIn = !empty($data['check_in']) ? date('H:i:s', strtotime($data['check_in'])) : '09:00:00';
                    $checkOut = !empty($data['check_out']) ? date('H:i:s', strtotime($data['check_out'])) : '17:00:00';

                    $attendanceData['check_in'] = $checkIn;
                    $attendanceData['check_out'] = $checkOut;
                    $attendanceData['leave_type_id'] = null;

                    // Calculate late/early minutes
                    $lateEarly = $this->calculateLateEarly($checkIn, $checkOut, $request->date);
                    $attendanceData['late_minutes'] = $lateEarly['late_minutes'];
                    $attendanceData['early_minutes'] = $lateEarly['early_minutes'];
                    $attendanceData['total_late_early_minutes'] = $lateEarly['total_minutes'];
                } else {
                    $attendanceData['check_in'] = null;
                    $attendanceData['check_out'] = null;
                    $attendanceData['leave_type_id'] = null;
                    $attendanceData['late_minutes'] = 0;
                    $attendanceData['early_minutes'] = 0;
                    $attendanceData['total_late_early_minutes'] = 0;
                }

                if ($existing) {
                    DB::table('attendance_records')->where('id', $existing->id)->update($attendanceData);
                } else {
                    $attendanceData['created_at'] = now();
                    DB::table('attendance_records')->insert($attendanceData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to save attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function report(Request $request)
    {
        if ($request->ajax()) {
            $month = $request->month ?? Carbon::now('Asia/Dhaka')->format('Y-m');
            $year = substr($month, 0, 4);
            $monthNum = substr($month, 5, 2);

            $employees = DB::table('employees')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->where('employees.status', 1)
                ->select('employees.id', 'employees.name', 'employees.employee_id', 'designations.name as designation')
                ->orderBy('employees.name')
                ->get();

            $data = [];
            foreach ($employees as $employee) {
                $attendance = DB::table('attendance_records')
                    ->where('employee_id', $employee->id)
                    ->whereYear('attendance_date', $year)
                    ->whereMonth('attendance_date', $monthNum)
                    ->select('status', DB::raw('COUNT(*) as total'))
                    ->groupBy('status')
                    ->get();

                $present = 0;
                $absent = 0;
                $late = 0;
                $halfDay = 0;

                foreach ($attendance as $a) {
                    if ($a->status == 'present') $present = $a->total;
                    if ($a->status == 'absent') $absent = $a->total;
                    if ($a->status == 'late') $late = $a->total;
                    if ($a->status == 'half_day') $halfDay = $a->total;
                }

                $totalDays = $present + $absent + $late + $halfDay;
                $attendancePercentage = $totalDays > 0 ? round(($present / $totalDays) * 100, 2) : 0;

                $data[] = (object)[
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'designation' => $employee->designation ?? 'N/A',
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'half_day' => $halfDay,
                    'total_days' => $totalDays,
                    'percentage' => $attendancePercentage
                ];
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('attendance_percentage', function ($row) {
                    $color = $row->percentage >= 90 ? 'success' : ($row->percentage >= 75 ? 'warning' : 'danger');
                    return '
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1" style="height: 6px; width: 100px;">
                                <div class="progress-bar bg-' . $color . '" role="progressbar" style="width: ' . $row->percentage . '%"></div>
                            </div>
                            <span class="ms-2">' . $row->percentage . '%</span>
                        </div>
                    ';
                })
                ->rawColumns(['attendance_percentage'])
                ->make(true);
        }

        $currentMonth = Carbon::now('Asia/Dhaka')->format('Y-m');
        return view('admin.hrm.attendance.report', compact('currentMonth'));
    }

    public function detailsReport(Request $request)
    {
        $month = $request->month ?? Carbon::now('Asia/Dhaka')->format('Y-m');
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $settings = DB::table('hrm_settings')->first();
        $defaultCheckIn = $settings->default_check_in ?? '09:00:00';
        $defaultCheckOut = $settings->default_check_out ?? '17:00:00';

        $weekends = DB::table('weekend_settings')->where('status', 1)->pluck('day_number')->toArray();

        $daysInMonth = Carbon::create($year, $monthNum, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $monthNum, $i);
            $dates[] = [
                'day' => $i,
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('D')
            ];
        }

        $employees = DB::table('employees')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('employees.status', 1)
            ->select('employees.id', 'employees.name', 'employees.employee_id', 'designations.name as designation')
            ->orderBy('employees.name')
            ->get();

        $attendances = DB::table('attendance_records')
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $monthNum)
            ->select('employee_id', 'attendance_date', 'status', 'check_in', 'check_out', 'leave_type_id', 'remarks', 'late_minutes', 'early_minutes')
            ->get()
            ->groupBy('employee_id');

        $matrixData = [];
        foreach ($employees as $employee) {
            $row = [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'designation' => $employee->designation ?? 'N/A',
                'attendance' => [],
                'present_count' => 0,
                'absent_count' => 0
            ];

            $employeeAttendances = isset($attendances[$employee->id]) ? $attendances[$employee->id] : collect();

            foreach ($dates as $date) {
                $attendance = $employeeAttendances->firstWhere('attendance_date', $date['date']);

                $onLeave = DB::table('leave_applications')
                    ->where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('start_date', '<=', $date['date'])
                    ->where('end_date', '>=', $date['date'])
                    ->exists();

                $status = 'not_recorded';
                if ($attendance) {
                    $status = $attendance->status;
                    if ($status == 'present') {
                        $row['present_count']++;
                    } elseif ($status == 'absent') {
                        $row['absent_count']++;
                    }
                } elseif ($onLeave) {
                    $status = 'on_leave';
                }

                $isWeekend = in_array(Carbon::parse($date['date'])->dayOfWeek, $weekends);

                // In the detailsReport method, when building attendance array:
                $row['attendance'][] = [
                    'day' => $date['day'],
                    'date' => $date['date'],
                    'day_name' => $date['day_name'],
                    'status' => $status,
                    'check_in' => $attendance->check_in ?? null,
                    'check_out' => $attendance->check_out ?? null,
                    'leave_type_id' => $attendance->leave_type_id ?? null,
                    'remarks' => $attendance->remarks ?? null,
                    'is_weekend' => $isWeekend,
                    'late_minutes' => $attendance->late_minutes ?? 0,      // Add this
                    'early_minutes' => $attendance->early_minutes ?? 0     // Add this
                ];
            }

            $row['total_days'] = count($dates);
            $row['attendance_percentage'] = $row['total_days'] > 0 ? round(($row['present_count'] / $row['total_days']) * 100, 2) : 0;

            $matrixData[] = $row;
        }

        $leaveTypes = DB::table('leave_types')->where('status', 1)->get();

        return view('admin.hrm.attendance.details-report', compact('matrixData', 'dates', 'month', 'year', 'monthNum', 'leaveTypes', 'defaultCheckIn', 'defaultCheckOut'));
    }

    public function updateSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date;
        $employeeId = $request->employee_id;

        if (Carbon::parse($date)->isFuture()) {
            return response()->json(['error' => 'Cannot edit future dates!'], 422);
        }

        try {
            $existing = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->where('attendance_date', $date)
                ->first();

            $attendanceData = [
                'status' => $request->status,
                'remarks' => $request->remarks,
                'updated_at' => now(),
                'created_by' => auth()->id()
            ];

            if ($request->status == 'present') {
                $checkIn = !empty($request->check_in) ? date('H:i:s', strtotime($request->check_in)) : '09:00:00';
                $checkOut = !empty($request->check_out) ? date('H:i:s', strtotime($request->check_out)) : '17:00:00';

                $attendanceData['check_in'] = $checkIn;
                $attendanceData['check_out'] = $checkOut;
                $attendanceData['leave_type_id'] = null;

                $lateEarly = $this->calculateLateEarly($checkIn, $checkOut, $date);
                $attendanceData['late_minutes'] = $lateEarly['late_minutes'];
                $attendanceData['early_minutes'] = $lateEarly['early_minutes'];
                $attendanceData['total_late_early_minutes'] = $lateEarly['total_minutes'];
            } else {
                $attendanceData['check_in'] = null;
                $attendanceData['check_out'] = null;
                $attendanceData['leave_type_id'] = null;
                $attendanceData['late_minutes'] = 0;
                $attendanceData['early_minutes'] = 0;
                $attendanceData['total_late_early_minutes'] = 0;
            }

            if ($existing) {
                DB::table('attendance_records')->where('id', $existing->id)->update($attendanceData);
            } else {
                $attendanceData['employee_id'] = $employeeId;
                $attendanceData['attendance_date'] = $date;
                $attendanceData['created_at'] = now();
                DB::table('attendance_records')->insert($attendanceData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    private function calculateLateEarly($checkIn, $checkOut, $date)
    {
        // Get settings
        $settings = DB::table('hrm_settings')->first();
        $lateGraceMinutes = $settings->late_grace_minutes ?? 15;
        $earlyGraceMinutes = $settings->early_grace_minutes ?? 15;

        $defaultCheckIn = $settings->default_check_in ?? '09:00:00';
        $defaultCheckOut = $settings->default_check_out ?? '17:00:00';

        // Check if date is weekend
        $weekends = DB::table('weekend_settings')->where('status', 1)->pluck('day_number')->toArray();
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, $weekends);

        $lateMinutes = 0;
        $earlyMinutes = 0;

        // Only calculate for working days
        if (!$isWeekend && $checkIn && $checkOut) {
            // Parse times
            $checkInTime = strtotime($checkIn);
            $checkOutTime = strtotime($checkOut);
            $defaultInTime = strtotime($defaultCheckIn);
            $defaultOutTime = strtotime($defaultCheckOut);

            // Calculate grace times in seconds
            $graceInTime = $defaultInTime + ($lateGraceMinutes * 60);
            $graceOutTime = $defaultOutTime - ($earlyGraceMinutes * 60);

            // Calculate late minutes (check-in is after grace time)
            if ($checkInTime > $graceInTime) {
                $lateSeconds = $checkInTime - $graceInTime;
                $lateMinutes = floor($lateSeconds / 60);
            }

            // Calculate early minutes (check-out is before grace time)
            if ($checkOutTime < $graceOutTime) {
                $earlySeconds = $graceOutTime - $checkOutTime;
                $earlyMinutes = floor($earlySeconds / 60);
            }
        }

        return [
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'total_minutes' => $lateMinutes + $earlyMinutes
        ];
    }
}