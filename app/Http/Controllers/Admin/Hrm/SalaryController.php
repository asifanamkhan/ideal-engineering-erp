<?php
// app/Http/Controllers/Admin/Hrm/SalaryController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('salary_records')
                ->leftJoin('salary_details', 'salary_records.id', '=', 'salary_details.salary_record_id')
                ->select(
                    'salary_records.*',
                    DB::raw('COUNT(DISTINCT salary_details.employee_id) as employees_count')
                )
                ->groupBy('salary_records.id')
                ->orderBy('salary_records.id', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('month_year', function ($row) {
                    return date('F Y', strtotime($row->month_year . '-01'));
                })
                ->addColumn('employees_count', function ($row) {
                    return '<span class="badge bg-primary">' . ($row->employees_count ?? 0) . '</span>';
                })
                ->addColumn('total_salary_formatted', function ($row) {
                    return '৳ ' . number_format($row->total_salary, 2);
                })
                ->addColumn('paid_amount_formatted', function ($row) {
                    return '৳ ' . number_format($row->paid_amount, 2);
                })
                ->addColumn('due_amount_formatted', function ($row) {
                    $due = $row->total_salary - $row->paid_amount;
                    return '৳ ' . number_format($due, 2);
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'draft' => 'badge bg-secondary',
                        'generated' => 'badge bg-info',
                        'paid' => 'badge bg-success',
                        'partial' => 'badge bg-warning'
                    ];
                    $badgeClass = $badges[$row->status] ?? 'badge bg-secondary';
                    return '<span class="' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('generated_date_formatted', function ($row) {
                    return date('d M Y', strtotime($row->generated_date));
                })
                ->addColumn('action', function ($row) {
                    return view('admin.hrm.salary.partials.action-btn-view', [
                        'id' => $row->id,
                        'status' => $row->status
                    ])->render();
                })
                ->rawColumns(['employees_count', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.hrm.salary.index');
    }

    public function create()
    {
        // Get all active employees with their salary data
        $employees = DB::table('employees')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('employees.status', 1)
            ->select(
                'employees.id',
                'employees.name',
                'employees.employee_id',
                'designations.name as designation',
                'employees.basic_salary',
                'employees.total_allowance',
                'employees.total_deduction',
                'employees.gross_salary'
            )
            ->orderBy('employees.name')
            ->get();

        return view('admin.hrm.salary.generate', compact('employees'));
    }

    public function generateDetails(Request $request)
    {
        $monthYear = $request->month_year;
        $year = substr($monthYear, 0, 4);
        $month = substr($monthYear, 5, 2);
        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Check if salary already exists for this month (for edit purpose)
        $existingSalary = DB::table('salary_records')->where('month_year', $monthYear)->first();
        $existingDetails = [];

        if ($existingSalary) {
            $existingDetails = DB::table('salary_details')
                ->where('salary_record_id', $existingSalary->id)
                ->get()
                ->keyBy('employee_id');
        }

        $employees = DB::table('employees')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('employees.status', 1)
            ->select(
                'employees.id',
                'employees.name',
                'employees.employee_id',
                'designations.name as designation',
                'employees.basic_salary',
                'employees.total_allowance',
                'employees.total_deduction',
                'employees.gross_salary'
            )
            ->orderBy('employees.name')
            ->get();

        $salaryDetails = [];

        foreach ($employees as $employee) {
            // If editing existing, use saved values
            if (isset($existingDetails[$employee->id])) {
                $saved = $existingDetails[$employee->id];
                $salaryDetails[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'designation' => $employee->designation ?? 'N/A',
                    'basic_salary' => $saved->basic_salary,
                    'total_allowance' => $saved->total_allowance,
                    'total_deduction' => $saved->total_deduction,
                    'unpaid_leave_days' => $saved->unpaid_leave_days,
                    'unpaid_deduction' => $saved->unpaid_leave_deduction,
                    'gross_salary' => $saved->gross_salary,
                    'net_salary' => $saved->net_salary,
                    'remarks' => $saved->remarks
                ];
                continue;
            }

            // CALCULATE unpaid leave days from ATTENDANCE
            $unpaidFromAttendance = DB::table('attendance_records')
                ->leftJoin('leave_types', 'attendance_records.leave_type_id', '=', 'leave_types.id')
                ->where('attendance_records.employee_id', $employee->id)
                ->whereBetween('attendance_records.attendance_date', [$startDate, $endDate])
                ->where('attendance_records.status', 'absent')
                ->where(function($query) {
                    $query->where('leave_types.is_paid', 0)
                          ->orWhereNull('attendance_records.leave_type_id');
                })
                ->count();

            // CALCULATE unpaid leave days from LEAVE APPLICATIONS
            $unpaidFromLeaves = DB::table('leave_applications')
                ->leftJoin('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
                ->where('leave_applications.employee_id', $employee->id)
                ->where('leave_applications.status', 'approved')
                ->where('leave_types.is_paid', 0)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('leave_applications.start_date', [$startDate, $endDate])
                          ->orWhereBetween('leave_applications.end_date', [$startDate, $endDate]);
                })
                ->sum(DB::raw('DATEDIFF(leave_applications.end_date, leave_applications.start_date) + 1'));

            // TOTAL UNPAID DAYS
            $totalUnpaidDays = $unpaidFromAttendance + $unpaidFromLeaves;

            // CALCULATE per day salary (basic / 30)
            $perDaySalary = $employee->basic_salary / 30;
            $unpaidDeduction = $totalUnpaidDays * $perDaySalary;

            // CALCULATE gross and net salary
            $grossSalary = ($employee->basic_salary + $employee->total_allowance) - $employee->total_deduction;
            $netSalary = $grossSalary - $unpaidDeduction;

            $salaryDetails[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'designation' => $employee->designation ?? 'N/A',
                'basic_salary' => $employee->basic_salary ?? 0,
                'total_allowance' => $employee->total_allowance ?? 0,
                'total_deduction' => $employee->total_deduction ?? 0,
                'unpaid_leave_days' => $totalUnpaidDays,
                'unpaid_deduction' => round($unpaidDeduction),
                'gross_salary' => round($grossSalary),
                'net_salary' => round($netSalary),
                'remarks' => null
            ];
        }

        return response()->json($salaryDetails);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month_year' => 'required|date_format:Y-m',
            'salary_data' => 'required|array',
            'salary_data.*.employee_id' => 'required|exists:employees,id',
            'salary_data.*.basic_salary' => 'required|numeric|min:0',
            'salary_data.*.total_allowance' => 'nullable|numeric|min:0',
            'salary_data.*.total_deduction' => 'nullable|numeric|min:0',
            'salary_data.*.unpaid_leave_days' => 'nullable|integer|min:0',
            'salary_data.*.unpaid_deduction' => 'nullable|numeric|min:0',
            'salary_data.*.gross_salary' => 'required|numeric|min:0',
            'salary_data.*.net_salary' => 'required|numeric|min:0',
            'salary_data.*.remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $existing = DB::table('salary_records')->where('month_year', $request->month_year)->first();
            if ($existing) {
                DB::table('salary_details')->where('salary_record_id', $existing->id)->delete();
                $salaryRecordId = $existing->id;
            } else {
                $salaryRecordId = DB::table('salary_records')->insertGetId([
                    'month_year' => $request->month_year,
                    'total_salary' => 0,
                    'paid_amount' => 0,
                    'due_amount' => 0,
                    'status' => 'draft',
                    'generated_date' => date('Y-m-d'),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $totalSalary = 0;

            foreach ($request->salary_data as $data) {
                // Use original unpaid days from request, don't recalculate
                $unpaidDays = $data['unpaid_leave_days'] ?? 0;
                $unpaidDeduction = $data['unpaid_deduction'] ?? 0;

                $netSalary = $data['net_salary'];
                $totalSalary += $netSalary;

                DB::table('salary_details')->insert([
                    'salary_record_id' => $salaryRecordId,
                    'employee_id' => $data['employee_id'],
                    'basic_salary' => $data['basic_salary'],
                    'total_allowance' => $data['total_allowance'] ?? 0,
                    'total_deduction' => $data['total_deduction'] ?? 0,
                    'unpaid_leave_days' => $unpaidDays,  // Keep original
                    'unpaid_leave_deduction' => $unpaidDeduction,  // Manual adjustment
                    'gross_salary' => $data['gross_salary'],
                    'net_salary' => $netSalary,
                    'remarks' => $data['remarks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::table('salary_records')->where('id', $salaryRecordId)->update([
                'total_salary' => $totalSalary,
                'due_amount' => $totalSalary,
                'status' => 'generated',
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salary generated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to generate salary: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $salaryRecord = DB::table('salary_records')->where('id', $id)->first();
        if (!$salaryRecord) {
            return redirect()->route('admin.hrm.salary.index')->with('error', 'Salary record not found');
        }

        $salaryDetails = DB::table('salary_details')
            ->leftJoin('employees', 'salary_details.employee_id', '=', 'employees.id')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('salary_details.salary_record_id', $id)
            ->select(
                'salary_details.*',
                'employees.name',
                'employees.employee_id as employee_code',
                'designations.name as designation'
            )
            ->orderBy('employees.name')
            ->get();

        $monthName = date('F Y', strtotime($salaryRecord->month_year . '-01'));

        return view('admin.hrm.salary.view', compact('salaryRecord', 'salaryDetails', 'monthName'));
    }


    public function destroy($id)
    {
        try {
            DB::table('salary_details')->where('salary_record_id', $id)->delete();
            DB::table('salary_records')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Salary record deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete salary record'], 500);
        }
    }

    public function markPaid($id)
    {
        try {
            $salaryRecord = DB::table('salary_records')->where('id', $id)->first();

            DB::table('salary_records')->where('id', $id)->update([
                'status' => 'paid',
                'paid_date' => date('Y-m-d'),
                'paid_amount' => $salaryRecord->total_salary,
                'due_amount' => 0,
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Salary marked as paid!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }
    public function getUnpaidHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $monthYear = $request->month_year;
        $year = substr($monthYear, 0, 4);
        $month = substr($monthYear, 5, 2);
        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $history = [];
        $totalUnpaidDays = 0;

        // 1. Get unpaid leaves from attendance (where employee was marked absent with unpaid leave)
        $attendanceRecords = DB::table('attendance_records')
            ->leftJoin('leave_types', 'attendance_records.leave_type_id', '=', 'leave_types.id')
            ->where('attendance_records.employee_id', $employeeId)
            ->whereBetween('attendance_records.attendance_date', [$startDate, $endDate])
            ->where('attendance_records.status', 'absent')
            ->where(function ($query) {
                $query->where('leave_types.is_paid', 0)
                    ->orWhereNull('attendance_records.leave_type_id');
            })
            ->select(
                'attendance_records.attendance_date as date',
                DB::raw("'Unpaid Absence' as type"),
                'attendance_records.remarks'
            )
            ->orderBy('attendance_records.attendance_date')
            ->get();

        foreach ($attendanceRecords as $record) {
            $history[] = [
                'date' => date('d M Y', strtotime($record->date)),
                'type' => $record->type,
                'remarks' => $record->remarks ?? 'Marked as absent (Unpaid)'
            ];
            $totalUnpaidDays++;
        }

        // 2. Get unpaid leaves from leave applications (approved unpaid leave applications)
        $leaveApplications = DB::table('leave_applications')
            ->leftJoin('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
            ->where('leave_applications.employee_id', $employeeId)
            ->where('leave_applications.status', 'approved')
            ->where('leave_types.is_paid', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('leave_applications.start_date', [$startDate, $endDate])
                    ->orWhereBetween('leave_applications.end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('leave_applications.start_date', '<=', $startDate)
                            ->where('leave_applications.end_date', '>=', $endDate);
                    });
            })
            ->select(
                'leave_applications.start_date',
                'leave_applications.end_date',
                'leave_applications.reason',
                'leave_applications.total_days',
                DB::raw("'Leave Application' as type")
            )
            ->orderBy('leave_applications.start_date')
            ->get();

        foreach ($leaveApplications as $leave) {
            $days = $leave->total_days;
            if (!$days) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                $days = $start->diffInDays($end) + 1;
            }
            $totalUnpaidDays += $days;

            $dateRange = date('d M Y', strtotime($leave->start_date)) . ' - ' . date('d M Y', strtotime($leave->end_date));
            $history[] = [
                'date' => $dateRange . ' (' . $days . ' days)',
                'type' => $leave->type,
                'remarks' => $leave->reason ?? 'Unpaid leave application'
            ];
        }

        // Sort by date (for attendance records)
        usort($history, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return response()->json([
            'total_unpaid_days' => $totalUnpaidDays,
            'history' => $history
        ]);
    }
}
