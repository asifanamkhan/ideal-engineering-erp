<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JobDescriptionsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $job_descriptions = DB::table('job_descriptions')->select('*')
                ->orderBy('is_default', 'DESC')
                ->orderBy('id', 'desc');

            return DataTables::of($job_descriptions)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($job_description) {
                    return $job_description->id;
                })
                ->addColumn('status_badge', function ($job_description) {
                    $status = $job_description->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('is_default', function ($job_description) {
                    $status = $job_description->is_default == 1 ? 'Yes' : '';
                    $badgeClass = $status === 'Yes' ? 'badge bg-primary' : ' ';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($job_description) {
                    return view('admin.settings.job_descriptions.partials.action-btn-view', ['id' => $job_description->id])->render();
                })
                ->rawColumns(['status_badge', 'action', 'is_default'])
                ->make(true);
        }

        return view('admin.settings.job_descriptions.index');
    }

    public function create()
    {
        return view('admin.settings.job_descriptions.partials.create-modal');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'status' => ['nullable', 'in:0,1'],
            'is_default' => ['nullable', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'description' => $request->description,
            'status' => $request->status ?? 1,
            'is_default' => $request->is_default ?? 0,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $id = DB::table('job_descriptions')->insertGetId($data);

        // If this is is_default, update others
        if($data['is_default'] == 1){
            DB::table('job_descriptions')
                ->where('id', '!=', $id)
                ->update(['is_default' => 0]);
        }

        // Return the newly created record
        $newDescription = DB::table('job_descriptions')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Job description created successfully!',
            'data' => $newDescription
        ]);
    }

    public function edit($id)
    {
        try {
            $job_description = DB::table('job_descriptions')->where('id', $id)->first();
            // Return JSON data instead of view
            return response()->json([
                'id' => $job_description->id,
                'status' => $job_description->status,
                'description' => $job_description->description,
                'is_default' => $job_description->is_default
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Job description not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
            'is_default' => ['nullable', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'status' => $request->status ?? 1,
            'is_default' => $request->is_default ?? 0,
            'description' => $request->description,
            'updated_at' => now()
        ];

        DB::table('job_descriptions')->where('id', $id)->update($data);

        // If this is is_default, update others
        if ($data['is_default'] == 1) {
            DB::table('job_descriptions')
                ->where('id', '!=', $id)
                ->update(['is_default' => 0]);
        }

        return response()->json(['success' => true, 'message' => 'Job description updated successfully!']);
    }

    public function destroy($id)
    {
        try {
            $job_description = DB::table('job_descriptions')
                ->where('id', $id)->first();
            $job_description->delete();

            return response()->json(['success' => true, 'message' => 'Job description deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete job_description'], 500);
        }
    }
}