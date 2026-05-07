<?php
// app/Http/Controllers/Admin/Hrm/OvertimeController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OvertimeController extends Controller
{
    private function getDefaultOvertimeHour()
    {
        $setting = DB::table('hrm_settings')->first();
        return $setting->default_overtime_hour ?? 4;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('overtime_records')
                ->select(
                    'overtime_date',
                    DB::raw('COUNT(DISTINCT employee_id) as employees_count'),
                    DB::raw('SUM(overtime_amount) as total_amount'),
                    DB::raw('SUM(CASE WHEN status = "paid" THEN overtime_amount ELSE 0 END) as paid_amount')
                )
                ->groupBy('overtime_date')
                ->orderBy('overtime_date', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('overtime_date', function ($row) {
                    return date('d M Y', strtotime($row->overtime_date));
                })
                ->addColumn('employees_count_badge', function ($row) {
                    return '<span class="badge bg-primary">' . ($row->employees_count ?? 0) . '</span>';
                })
                ->addColumn('total_amount_formatted', function ($row) {
                    return '৳ ' . number_format($row->total_amount, 2);
                })
                ->addColumn('paid_amount_formatted', function ($row) {
                    return '৳ ' . number_format($row->paid_amount, 2);
                })
                ->addColumn('due_amount_formatted', function ($row) {
                    $due = $row->total_amount - $row->paid_amount;
                    return '৳ ' . number_format($due, 2);
                })
                ->addColumn('status_badge', function ($row) {
                    $due = $row->total_amount - $row->paid_amount;
                    if ($due <= 0) {
                        return '<span class="badge bg-success">Paid</span>';
                    } elseif ($row->paid_amount > 0) {
                        return '<span class="badge bg-warning">Partial</span>';
                    } else {
                        return '<span class="badge bg-danger">Unpaid</span>';
                    }
                })
                ->addColumn('generated_date', function ($row) {
                    $record = DB::table('overtime_records')
                        ->where('overtime_date', $row->overtime_date)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    return $record ? date('d M Y', strtotime($record->created_at)) : date('d M Y');
                })
                ->addColumn('action', function ($row) {
                    return view('admin.hrm.overtime.partials.action-btn-view', [
                        'date' => $row->overtime_date
                    ])->render();
                })
                ->rawColumns(['employees_count_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.hrm.overtime.index');
    }

    public function viewByDate($date)
    {
        $records = DB::table('overtime_records')
            ->leftJoin('employees', 'overtime_records.employee_id', '=', 'employees.id')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('overtime_records.overtime_date', $date)
            ->select(
                'overtime_records.*',
                'employees.name',
                'employees.employee_id as employee_code',
                'designations.name as designation'
            )
            ->orderBy('employees.name')
            ->get();

        $totalAmount = $records->sum('overtime_amount');
        $dateFormatted = date('d M Y', strtotime($date));
        $totalHours = $records->sum('overtime_hours');

        return view('admin.hrm.overtime.partials.view-content', compact('records', 'dateFormatted', 'date', 'totalAmount', 'totalHours'));
    }

    // Date-wise overtime entry form
    public function dateWise()
    {
        $employees = DB::table('employees')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('employees.status', 1)
            ->select(
                'employees.id',
                'employees.name',
                'employees.employee_id',
                'designations.name as designation',
                'employees.overtime_rate'
            )
            ->orderBy('employees.name')
            ->get();

        $weekends = DB::table('weekend_settings')
            ->where('status', 1)
            ->pluck('day_number')
            ->toArray();

        return view('admin.hrm.overtime.date-wise', compact('employees', 'weekends'));
    }

    public function getDateWiseDetails(Request $request)
    {
        $date = $request->date;
        $currentDate = date('Y-m-d');

        if ($date > $currentDate) {
            return response()->json(['error' => 'Cannot add overtime for future dates!'], 422);
        }

        // CHECK 1: Attendance taken or not?
        $attendanceExists = DB::table('attendance_records')
            ->where('attendance_date', $date)
            ->exists();

         // CHECK 1: Attendance taken or not?
        $attendanceExists = DB::table('attendance_records')
        ->where('attendance_date', $date)
        ->exists();

        if (!$attendanceExists) {
            $formattedDate = date('d M Y', strtotime($date));
            return response()->json([
                'error' => "⚠️ Attendance has not been taken for {$formattedDate}. Please take attendance first before adding overtime.",
                'attendance_missing' => true
            ], 422);
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $weekends = DB::table('weekend_settings')
            ->where('status', 1)
            ->pluck('day_number')
            ->toArray();
        $isWeekend = in_array($dayOfWeek, $weekends);

        $weekendNames = DB::table('weekend_settings')
            ->where('status', 1)
            ->pluck('day_name')
            ->implode(', ');

        // Get employees who are PRESENT on this date (not absent, not on leave)
        $presentEmployees = DB::table('attendance_records')
            ->where('attendance_date', $date)
            ->where('status', 'present')
            ->pluck('employee_id')
            ->toArray();

        // Get employees on leave (so we can show message)
        $employeesOnLeave = DB::table('leave_applications')
            ->leftJoin('employees', 'leave_applications.employee_id', '=', 'employees.id')
            ->where('leave_applications.status', 'approved')
            ->where('leave_applications.start_date', '<=', $date)
            ->where('leave_applications.end_date', '>=', $date)
            ->pluck('employees.name', 'leave_applications.employee_id')
            ->toArray();

        // Get employees who are absent
        $absentEmployees = DB::table('attendance_records')
            ->where('attendance_date', $date)
            ->where('status', 'absent')
            ->pluck('employee_id')
            ->toArray();

        // Get all active employees who are PRESENT (not absent, not on leave)
        $employees = DB::table('employees')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('employees.status', 1)
            ->whereIn('employees.id', $presentEmployees)
            ->whereNotIn('employees.id', array_keys($employeesOnLeave))
            ->select(
                'employees.id',
                'employees.name',
                'employees.employee_id',
                'designations.name as designation',
                'employees.overtime_rate',
                'employees.default_overtime_hour'
            )
            ->orderBy('employees.name')
            ->get();

        // Get existing overtime records
        $existingOvertime = DB::table('overtime_records')
            ->where('overtime_date', $date)
            ->get()
            ->keyBy('employee_id');

        $data = [];
        foreach ($employees as $employee) {
            $existing = $existingOvertime[$employee->id] ?? null;

            // Rate: existing rate > overtime_rate > 0
            $rate = 0;
            if ($existing && $existing->overtime_rate > 0) {
                $rate = $existing->overtime_rate;
            } elseif ($employee->overtime_rate > 0) {
                $rate = $employee->overtime_rate;
            }

            // Hours: existing hours > default_overtime_hour > 0
            $hours = 0;
            if ($existing && $existing->overtime_hours > 0) {
                $hours = $existing->overtime_hours;
            } elseif ($employee->default_overtime_hour > 0) {
                $hours = $employee->default_overtime_hour;
            }

            $data[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'designation' => $employee->designation ?? 'N/A',
                'overtime_rate' => $isWeekend ? 0 : $rate,
                'overtime_hours' => $hours,
                'overtime_amount' => ($isWeekend ? 0 : $rate) * $hours,
                'remarks' => $existing->remarks ?? null,
                'record_id' => $existing->id ?? null,
                'is_weekend' => $isWeekend
            ];
        }

        // Get excluded employee names for message
        $excludedNames = [];
        foreach ($absentEmployees as $absentId) {
            $emp = DB::table('employees')->where('id', $absentId)->first();
            if ($emp) {
                $excludedNames[] = $emp->name . ' (Absent)';
            }
        }
        foreach ($employeesOnLeave as $leaveId => $leaveName) {
            $excludedNames[] = $leaveName . ' (On Leave)';
        }

        return response()->json([
            'data' => $data,
            'is_weekend' => $isWeekend,
            'weekend_names' => $weekendNames,
            'weekend_message' => $isWeekend ? "⚠️ Warning: {$date} is a {$weekendNames}! Overtime rate has been set to 0 by default. You can still change it manually if needed." : null,
            'excluded_employees' => $excludedNames,
            'attendance_exists' => true
        ]);
    }

    public function getEmployeeWiseDetails(Request $request)
    {
        $employeeId = $request->employee_id;
        $monthYear = $request->month_year;
        $year = substr($monthYear, 0, 4);
        $month = substr($monthYear, 5, 2);

        // Get employee details
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->select('id', 'name', 'employee_id', 'overtime_rate', 'default_overtime_hour')
            ->first();

        // Get weekend settings
        $weekends = DB::table('weekend_settings')
            ->where('status', 1)
            ->pluck('day_number')
            ->toArray();

        $weekendNames = DB::table('weekend_settings')
            ->where('status', 1)
            ->pluck('day_name')
            ->implode(', ');

        // Get today's date
        $today = Carbon::now('Asia/Dhaka')->toDateString();

        // Get all dates in the month UP TO TODAY only
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i);
            $dateString = $date->format('Y-m-d');

            // Skip future dates
            if ($dateString > $today) {
                continue;
            }

            // Get attendance status for this date
            $attendance = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->where('attendance_date', $dateString)
                ->first();

            // Check if on leave
            $onLeave = DB::table('leave_applications')
                ->where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where('start_date', '<=', $dateString)
                ->where('end_date', '>=', $dateString)
                ->exists();

            $attendanceStatus = 'not_recorded';
            if ($attendance) {
                $attendanceStatus = $attendance->status;
            } elseif ($onLeave) {
                $attendanceStatus = 'on_leave';
            }

            $dates[] = [
                'day' => $i,
                'date' => $dateString,
                'day_name' => $date->format('D'),
                'is_weekend' => in_array($date->dayOfWeek, $weekends),
                'attendance_status' => $attendanceStatus  // present, absent, on_leave, not_recorded
            ];
        }

        // Get existing overtime records
        $existingOvertime = DB::table('overtime_records')
            ->where('employee_id', $employeeId)
            ->whereYear('overtime_date', $year)
            ->whereMonth('overtime_date', $month)
            ->get()
            ->keyBy('overtime_date');

        $data = [];
        foreach ($dates as $date) {
            $existing = $existingOvertime[$date['date']] ?? null;

            // Check if employee can get overtime (must be present)
            $canAddOvertime = ($date['attendance_status'] == 'present') && !$date['is_weekend'];

            // Rate: existing rate > overtime_rate > 0
            $rate = 0;
            if ($existing && $existing->overtime_rate > 0) {
                $rate = $existing->overtime_rate;
            } elseif ($employee->overtime_rate > 0) {
                $rate = $employee->overtime_rate;
            }

            // Hours: existing hours > default_overtime_hour > 0
            $hours = 0;
            if ($existing && $existing->overtime_hours > 0) {
                $hours = $existing->overtime_hours;
            } elseif ($employee->default_overtime_hour > 0) {
                $hours = $employee->default_overtime_hour;
            }

            $data[] = [
                'date' => $date['date'],
                'day' => $date['day'],
                'day_name' => $date['day_name'],
                'is_weekend' => $date['is_weekend'],
                'attendance_status' => $date['attendance_status'],
                'can_add_overtime' => ($date['attendance_status'] == 'present') && !$date['is_weekend'],
                'overtime_rate' => ($date['is_weekend'] || !$canAddOvertime) ? 0 : $rate,
                'overtime_hours' => $canAddOvertime ? $hours : 0,
                'overtime_amount' => ($canAddOvertime && !$date['is_weekend']) ? $rate * $hours : 0,
                'remarks' => $existing->remarks ?? null,
                'record_id' => $existing->id ?? null
            ];
        }

        return response()->json([
            'employee' => $employee,
            'data' => $data,
            'weekends' => $weekends,
            'weekend_names' => $weekendNames,
            'today' => $today
        ]);
    }

    // Store date-wise overtime
    public function storeDateWise(Request $request)
    {
        $date = $request->date;
        $currentDate = date('Y-m-d');

        if ($date > $currentDate) {
            return response()->json(['error' => 'Cannot add overtime for future dates!'], 422);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'overtime_data' => 'required|array',
            'overtime_data.*.employee_id' => 'required|exists:employees,id',
            'overtime_data.*.overtime_hours' => 'nullable|numeric|min:0|max:24',
            'overtime_data.*.overtime_rate' => 'nullable|numeric|min:0',
            'overtime_data.*.remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->overtime_data as $data) {
                $hours = $data['overtime_hours'] ?? 0;
                $rate = $data['overtime_rate'] ?? 0;
                $amount = $hours * $rate;

                $existing = DB::table('overtime_records')
                    ->where('employee_id', $data['employee_id'])
                    ->where('overtime_date', $request->date)
                    ->first();

                $overtimeData = [
                    'employee_id' => $data['employee_id'],
                    'overtime_date' => $request->date,
                    'overtime_rate' => $rate,
                    'overtime_hours' => $hours,
                    'overtime_amount' => $amount,
                    'remarks' => $data['remarks'] ?? null,
                    'updated_at' => now()
                ];

                if ($existing) {
                    DB::table('overtime_records')->where('id', $existing->id)->update($overtimeData);
                } else {
                    $overtimeData['created_by'] = auth()->id();
                    $overtimeData['created_at'] = now();
                    DB::table('overtime_records')->insert($overtimeData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Overtime saved successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    // Delete all overtime for a date
    public function deleteByDate($date)
    {
        try {
            DB::table('overtime_records')->where('overtime_date', $date)->delete();
            return response()->json(['success' => true, 'message' => 'Overtime records deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete'], 500);
        }
    }

    // Mark as paid for a date
    public function markAsPaid($date)
    {
        try {
            DB::table('overtime_records')
                ->where('overtime_date', $date)
                ->update(['status' => 'paid', 'updated_at' => now()]);
            return response()->json(['success' => true, 'message' => 'Overtime marked as paid!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update'], 500);
        }
    }

    // Employee-wise overtime form
    public function employeeWise()
    {
        $employees = DB::table('employees')
            ->where('status', 1)
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->get();

        return view('admin.hrm.overtime.employee-wise', compact('employees'));
    }

    public function storeEmployeeWise(Request $request)
{
    $validator = Validator::make($request->all(), [
        'employee_id' => 'required|exists:employees,id',
        'month_year' => 'required|date_format:Y-m',
        'overtime_data' => 'required|array',
        'overtime_data.*.date' => 'required|date',
        'overtime_data.*.overtime_hours' => 'nullable|numeric|min:0|max:24',
        'overtime_data.*.overtime_rate' => 'nullable|numeric|min:0',
        'overtime_data.*.remarks' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        DB::beginTransaction();

        foreach ($request->overtime_data as $data) {
            $hours = $data['overtime_hours'] ?? 0;
            $rate = $data['overtime_rate'] ?? 0;
            $amount = $hours * $rate;

            $existing = DB::table('overtime_records')
                ->where('employee_id', $request->employee_id)
                ->where('overtime_date', $data['date'])
                ->first();

            // If hours is 0, delete existing record if any
            if ($hours == 0) {
                if ($existing) {
                    DB::table('overtime_records')->where('id', $existing->id)->delete();
                }
                continue;
            }

            // If hours > 0, save or update
            $overtimeData = [
                'employee_id' => $request->employee_id,
                'overtime_date' => $data['date'],
                'overtime_rate' => $rate,
                'overtime_hours' => $hours,
                'overtime_amount' => $amount,
                'remarks' => $data['remarks'] ?? null,
                'updated_at' => now()
            ];

            if ($existing) {
                DB::table('overtime_records')->where('id', $existing->id)->update($overtimeData);
            } else {
                $overtimeData['created_by'] = auth()->id();
                $overtimeData['created_at'] = now();
                DB::table('overtime_records')->insert($overtimeData);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Overtime saved successfully!'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Failed to save: ' . $e->getMessage()], 500);
    }
}
}
