<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $designations = DB::table('designations')->select('*')->orderBy('id', 'desc');

            return DataTables::of($designations)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($designation) {
                    return $designation->id;
                })
                ->addColumn('status_badge', function ($designation) {
                    $status = $designation->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($designation) {
                    return view('admin.hrm.designations.partials.action-btn-view', ['id' => $designation->id])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.hrm.designations.index');
    }

    public function create()
    {
        return view('admin.hrm.designations.partials.create-modal');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191', 'unique:designations'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->input();
        // dd($data);
        $data['status'] = $request->status ?? 1;

        DB::table('designations')->insert([
            'name' => $data['name'],
            'status' => $data['status'],
            'description' => $data['description'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Designation created successfully!']);
    }

    public function edit($id)
    {
        try {
            $designation = DB::table('designations')->where('id', $id)->first();
            // Return JSON data instead of view
            return response()->json([
                'id' => $designation->id,
                'name' => $designation->name,
                'status' => $designation->status,
                'description' => $designation->description
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Part not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('designations')->ignore($id)
            ],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $designation = DB::table('designations')->where('id', $id)->first();
            $data = $request->all();
            $data['status'] = $request->status ?? $designation->status;

            DB::table('designations')->update([
                'name' => $data['name'],
                'status' => $data['status'],
                'description' => $data['description'],
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Part updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update designation'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $designation = DB::table('designations')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Part deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete designation'], 500);
        }
    }
}