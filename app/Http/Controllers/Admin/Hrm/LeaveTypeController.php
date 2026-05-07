<?php
// app/Http/Controllers/Admin/Hrm/LeaveTypeController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $leave_types = DB::table('leave_types')->select('*')
                ->orderBy('id', 'desc');

            return DataTables::of($leave_types)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($leave_type) {
                    return $leave_type->id;
                })
                ->addColumn('status_badge', function ($leave_type) {
                    $status = $leave_type->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('is_paid_badge', function ($leave_type) {
                    $isPaid = $leave_type->is_paid == 1 ? 'Paid' : 'Unpaid';
                    $badgeClass = $leave_type->is_paid == 1 ? 'badge bg-primary' : 'badge bg-warning';
                    return '<span class="' . $badgeClass . '">' . $isPaid . '</span>';
                })
                ->addColumn('action', function ($leave_type) {
                    return view('admin.hrm.leave_types.partials.action-btn-view', ['id' => $leave_type->id])->render();
                })
                ->rawColumns(['status_badge', 'is_paid_badge', 'action'])
                ->make(true);
        }

        return view('admin.hrm.leave_types.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:leave_types,code',
            'description' => 'nullable|string',
            'is_paid' => 'nullable|in:0,1',
            'max_days_per_year' => 'nullable|integer|min:0',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_paid' => $request->is_paid ?? 1,
            'max_days_per_year' => $request->max_days_per_year ?? 0,
            'status' => $request->status ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $id = DB::table('leave_types')->insertGetId($data);

        $newLeaveType = DB::table('leave_types')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Leave type created successfully!',
            'data' => $newLeaveType
        ]);
    }

    public function edit($id)
    {
        try {
            $leave_type = DB::table('leave_types')->where('id', $id)->first();
            return response()->json($leave_type);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Leave type not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:leave_types,code,' . $id,
            'description' => 'nullable|string',
            'is_paid' => 'nullable|in:0,1',
            'max_days_per_year' => 'nullable|integer|min:0',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_paid' => $request->is_paid ?? 1,
            'max_days_per_year' => $request->max_days_per_year ?? 0,
            'status' => $request->status ?? 1,
            'updated_at' => now()
        ];

        DB::table('leave_types')->where('id', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Leave type updated successfully!']);
    }

    public function destroy($id)
    {
        try {
            // Check if any leave application exists for this leave type
            $hasApplications = DB::table('leave_applications')->where('leave_type_id', $id)->exists();

            if ($hasApplications) {
                return response()->json(['error' => 'Cannot delete! This leave type has leave applications.'], 422);
            }

            DB::table('leave_types')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Leave type deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete leave type'], 500);
        }
    }
}
