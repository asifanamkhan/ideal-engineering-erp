<?php
// app/Http/Controllers/Admin/Hrm/LeaveApplicationController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveApplicationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('leave_applications')
                ->leftJoin('employees', 'leave_applications.employee_id', '=', 'employees.id')
                ->leftJoin('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
                ->select(
                    'leave_applications.*',
                    'employees.name as employee_name',
                    'employees.employee_id as employee_code',
                    'leave_types.name as leave_type_name',
                    'leave_types.code as leave_type_code',
                    'leave_types.is_paid'
                )
                ->orderBy('leave_applications.id', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($row) {
                    return $row->id;
                })
                ->addColumn('employee_info', function ($row) {
                    return '
                        <div>
                            <strong>' . e($row->employee_name) . '</strong><br>
                            <small class="text-muted">ID: ' . e($row->employee_code) . '</small>
                        </div>
                    ';
                })
                ->addColumn('leave_info', function ($row) {
                    $paidBadge = $row->is_paid == 1 ? '<span class="badge bg-primary">Paid</span>' : '<span class="badge bg-warning">Unpaid</span>';
                    return '
                        <div>
                            <strong>' . e($row->leave_type_name) . '</strong><br>
                            <small>' . $paidBadge . '</small>
                        </div>
                    ';
                })
                ->addColumn('date_range', function ($row) {
                    return '
                        <div>
                            <i class="fas fa-calendar-alt me-1">&nbsp;</i>' . date('d M Y', strtotime($row->start_date)) . '<br>
                            <i class="fas fa-calendar-alt me-1">&nbsp;</i>' . date('d M Y', strtotime($row->end_date)) . '
                        </div>
                    ';
                })
                ->addColumn('total_days', function ($row) {
                    return '<span class="badge bg-info">' . $row->total_days . ' days</span>';
                })
                ->addColumn('reason', function ($row) {
                    return $row->reason ?? '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => 'badge bg-warning',
                        'approved' => 'badge bg-success',
                        'rejected' => 'badge bg-danger',
                        'cancelled' => 'badge bg-secondary'
                    ];
                    $badgeClass = $badges[$row->status] ?? 'badge bg-secondary';
                    return '<span class="' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return view('admin.hrm.leave_applications.partials.action-btn-view', [
                        'id' => $row->id,
                        'status' => $row->status
                    ])->render();
                })
                ->rawColumns(['employee_info', 'leave_info', 'date_range', 'total_days', 'status_badge', 'action'])
                ->make(true);
        }

        // Get employees for dropdown
        $employees = DB::table('employees')->where('status', 1)->get();
        $leaveTypes = DB::table('leave_types')->where('status', 1)->get();

        return view('admin.hrm.leave_applications.index', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|integer|min:1',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalDays = $request->total_days;

        // Check balance before creating (for paid leaves)
        $leaveType = DB::table('leave_types')->where('id', $request->leave_type_id)->first();

        if ($leaveType->is_paid == 1 && $leaveType->max_days_per_year > 0) {
            $usedDays = $this->getUsedLeaveDays($request->employee_id, $request->leave_type_id);
            $availableDays = $leaveType->max_days_per_year - $usedDays;

            if ($totalDays > $availableDays) {
                return response()->json([
                    'error' => 'Insufficient leave balance! Available: ' . $availableDays . ' days, Requested: ' . $totalDays . ' days'
                ], 422);
            }
        }

        $data = [
            'employee_id' => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $id = DB::table('leave_applications')->insertGetId($data);

        return response()->json([
            'success' => true,
            'message' => 'Leave application submitted successfully!'
        ]);
    }

    public function edit($id)
    {
        try {
            $leave = DB::table('leave_applications')->where('id', $id)->first();

            if ($leave->status !== 'pending') {
                return response()->json(['error' => 'Only pending leaves can be edited'], 422);
            }

            return response()->json($leave);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Leave application not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $leave = DB::table('leave_applications')->where('id', $id)->first();

        if ($leave->status !== 'pending') {
            return response()->json(['error' => 'Only pending leaves can be edited'], 422);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|integer|min:1',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'employee_id' => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $request->total_days,
            'reason' => $request->reason,
            'updated_at' => now()
        ];

        DB::table('leave_applications')->where('id', $id)->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Leave application updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        try {
            $leave = DB::table('leave_applications')->where('id', $id)->first();

            if ($leave->status === 'approved') {
                return response()->json(['error' => 'Approved leaves cannot be deleted'], 422);
            }

            DB::table('leave_applications')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Leave application deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete leave application'], 500);
        }
    }

    public function approve($id)
    {
        try {
            $leave = DB::table('leave_applications')->where('id', $id)->first();

            if ($leave->status !== 'pending') {
                return response()->json(['error' => 'Only pending leaves can be approved'], 422);
            }

            DB::table('leave_applications')->where('id', $id)->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Leave approved successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to approve leave'], 500);
        }
    }

    public function reject($id)
    {
        try {
            $leave = DB::table('leave_applications')->where('id', $id)->first();

            if ($leave->status !== 'pending') {
                return response()->json(['error' => 'Only pending leaves can be rejected'], 422);
            }

            DB::table('leave_applications')->where('id', $id)->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Leave rejected successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to reject leave'], 500);
        }
    }

    // Remove calculateDays method completely (no longer needed)

    public function checkBalance(Request $request)
    {
        $employeeId = $request->employee_id;
        $leaveTypeId = $request->leave_type_id;
        $currentYear = Carbon::now('Asia/Dhaka')->year;

        $usedDays = DB::table('leave_applications')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $leaveType = DB::table('leave_types')->where('id', $leaveTypeId)->first();
        $maxDays = $leaveType->max_days_per_year ?? 0;

        return response()->json([
            'used_days' => $usedDays,
            'max_days' => $maxDays,
            'available_days' => $maxDays - $usedDays
        ]);
    }

    private function getUsedLeaveDays($employeeId, $leaveTypeId)
    {
        $currentYear = Carbon::now('Asia/Dhaka')->year;

        $total = DB::table('leave_applications')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        return $total;
    }

    public function unapprove($id)
    {
        try {
            $leave = DB::table('leave_applications')->where('id', $id)->first();

            if ($leave->status !== 'approved') {
                return response()->json(['error' => 'Only approved leaves can be unapproved'], 422);
            }

            DB::table('leave_applications')->where('id', $id)->update([
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Leave unapproved successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to unapprove leave'], 500);
        }
    }

    public function balanceIndex(Request $request)
    {
        if ($request->ajax()) {
            $selectedYear = $request->year ?? date('Y');

            if ($selectedYear == 'all') {
                // Get all years data grouped by employee and year
                $employees = DB::table('employees')
                    ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                    ->where('employees.status', 1)
                    ->select(
                        'employees.id',
                        'employees.name',
                        'employees.employee_id',
                        'designations.name as designation'
                    )
                    ->orderBy('employees.name')
                    ->get();

                $leaveTypes = DB::table('leave_types')
                    ->where('status', 1)
                    ->select('id', 'name', 'code', 'max_days_per_year', 'is_paid')
                    ->orderBy('name')
                    ->get();

                $data = [];

                foreach ($employees as $employee) {
                    foreach ($leaveTypes as $leaveType) {
                        // Get data for each year that has records
                        $yearsWithData = DB::table('leave_applications')
                            ->where('employee_id', $employee->id)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('status', 'approved')
                            ->select(DB::raw('DISTINCT YEAR(start_date) as year'))
                            ->orderBy('year', 'desc')
                            ->get();

                        foreach ($yearsWithData as $yearData) {
                            $year = $yearData->year;
                            $usedDays = DB::table('leave_applications')
                                ->where('employee_id', $employee->id)
                                ->where('leave_type_id', $leaveType->id)
                                ->where('status', 'approved')
                                ->whereYear('start_date', $year)
                                ->sum('total_days');

                            $maxDays = $leaveType->max_days_per_year;
                            $available = $maxDays - $usedDays;

                            $data[] = (object)[
                                'employee_id' => $employee->employee_id,
                                'name' => $employee->name,
                                'designation' => $employee->designation ?? 'N/A',
                                'year' => $year,
                                'leave_balances' => [[
                                    'name' => $leaveType->name,
                                    'code' => $leaveType->code,
                                    'max' => $maxDays,
                                    'used' => $usedDays,
                                    'available' => $available > 0 ? $available : 0,
                                    'is_paid' => $leaveType->is_paid
                                ]],
                                'total_used' => $usedDays,
                                'total_max' => $maxDays,
                                'total_available' => $available > 0 ? $available : 0
                            ];
                        }
                    }
                }
            } else {
                $currentYear = $selectedYear;

                $employees = DB::table('employees')
                    ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                    ->where('employees.status', 1)
                    ->select(
                        'employees.id',
                        'employees.name',
                        'employees.employee_id',
                        'designations.name as designation'
                    )
                    ->orderBy('employees.name')
                    ->get();

                $leaveTypes = DB::table('leave_types')
                    ->where('status', 1)
                    ->select('id', 'name', 'code', 'max_days_per_year', 'is_paid')
                    ->orderBy('name')
                    ->get();

                $data = [];

                foreach ($employees as $employee) {
                    $leaveBalances = [];
                    $totalUsed = 0;
                    $totalMax = 0;
                    $totalAvailable = 0;

                    foreach ($leaveTypes as $leaveType) {
                        $usedDays = DB::table('leave_applications')
                            ->where('employee_id', $employee->id)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('status', 'approved')
                            ->whereYear('start_date', $currentYear)
                            ->sum('total_days');

                        $maxDays = $leaveType->max_days_per_year;
                        $available = $maxDays - $usedDays;

                        if ($maxDays > 0) {
                            $totalMax += $maxDays;
                            $totalUsed += $usedDays;
                            $totalAvailable += ($available > 0 ? $available : 0);
                        }

                        $leaveBalances[] = [
                            'name' => $leaveType->name,
                            'code' => $leaveType->code,
                            'max' => $maxDays,
                            'used' => $usedDays,
                            'available' => $available > 0 ? $available : 0,
                            'is_paid' => $leaveType->is_paid
                        ];
                    }

                    $data[] = (object)[
                        'employee_id' => $employee->employee_id,
                        'name' => $employee->name,
                        'designation' => $employee->designation ?? 'N/A',
                        'year' => $currentYear,
                        'leave_balances' => $leaveBalances,
                        'total_used' => $totalUsed,
                        'total_max' => $totalMax,
                        'total_available' => $totalAvailable
                    ];
                }
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employee_info', function ($row) {
                    return '
                <div>
                    <strong>' . e($row->name) . '</strong><br>
                    <small class="text-muted">ID: ' . e($row->employee_id) . '</small>
                </div>
            ';
                })
                ->addColumn('year', function ($row) {
                    return '<span class="badge bg-primary">' . $row->year . '</span>';
                })
                ->addColumn('designation', function ($row) {
                    return $row->designation;
                })
                ->addColumn('leave_balance_summary', function ($row) {
                    $html = '<div class="leave-balance-grid">';

                    foreach ($row->leave_balances as $balance) {
                        if ($balance['max'] > 0 || $balance['used'] > 0) {
                            $percentage = $balance['max'] > 0 ? ($balance['used'] / $balance['max']) * 100 : 0;
                            $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 75 ? 'bg-warning' : 'bg-success');
                            $paidBadge = $balance['is_paid'] == 1 ? '<span class="badge bg-primary ms-1" style="font-size: 9px;">Paid</span>' : '<span class="badge bg-warning ms-1" style="font-size: 9px;">Unpaid</span>';

                            $html .= '
                        <div class="leave-item mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="fw-bold">' . e($balance['name']) . '</span>
                                    <small class="text-muted">(' . e($balance['code']) . ')</small>
                                    ' . $paidBadge . '
                                </div>
                                <div>
                                    <span class="badge bg-info">Used: ' . $balance['used'] . '</span>
                                    <span class="badge bg-primary">Max: ' . ($balance['max'] > 0 ? $balance['max'] : '∞') . '</span>
                                    <span class="badge ' . ($balance['available'] > 0 ? 'bg-success' : 'bg-danger') . '">Avail: ' . $balance['available'] . '</span>
                                </div>
                            </div>
                            ' . ($balance['max'] > 0 ? '
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar ' . $progressClass . '" role="progressbar" style="width: ' . $percentage . '%"></div>
                            </div>
                            ' : '') . '
                        </div>
                    ';
                        }
                    }

                    $totalPercentage = $row->total_max > 0 ? ($row->total_used / $row->total_max) * 100 : 0;
                    $totalProgressClass = $totalPercentage >= 90 ? 'bg-danger' : ($totalPercentage >= 75 ? 'bg-warning' : 'bg-success');

                    $html .= '
                <div class="leave-item total-row mt-3 pt-2" style="border-top: 2px solid #4e73df;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <span class="fw-bold text-primary">TOTAL (All Leave Types)</span>
                        </div>
                        <div>
                            <span class="badge bg-info">Used: ' . $row->total_used . '</span>
                            <span class="badge bg-primary">Total Max: ' . $row->total_max . '</span>
                            <span class="badge ' . ($row->total_available > 0 ? 'bg-success' : 'bg-danger') . '">Available: ' . $row->total_available . '</span>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar ' . $totalProgressClass . '" role="progressbar" style="width: ' . $totalPercentage . '%"></div>
                    </div>
                </div>
            ';

                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['employee_info', 'year', 'leave_balance_summary'])
                ->make(true);
        }

        return view('admin.hrm.leave_applications.balance');
    }

}