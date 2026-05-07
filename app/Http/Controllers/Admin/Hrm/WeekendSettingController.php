<?php
// app/Http/Controllers/Admin/Hrm/WeekendSettingController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WeekendSettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $settings = DB::table('weekend_settings')
                ->select('*')
                ->orderBy('day_number');

            return DataTables::of($settings)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $status = $row->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return view('admin.hrm.weekend.partials.action-btn-view', ['id' => $row->id])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.hrm.weekend.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_name' => 'required|string|unique:weekend_settings,day_name',
            'day_number' => 'required|integer|unique:weekend_settings,day_number',
            'status' => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'day_name' => $request->day_name,
            'day_number' => $request->day_number,
            'is_weekend' => true,
            'status' => $request->status ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('weekend_settings')->insert($data);

        return response()->json(['success' => true, 'message' => 'Weekend setting added successfully!']);
    }

    public function edit($id)
    {
        $setting = DB::table('weekend_settings')->where('id', $id)->first();
        return response()->json($setting);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'day_name' => 'required|string|unique:weekend_settings,day_name,' . $id,
            'day_number' => 'required|integer|unique:weekend_settings,day_number,' . $id,
            'status' => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('weekend_settings')->where('id', $id)->update([
            'day_name' => $request->day_name,
            'day_number' => $request->day_number,
            'status' => $request->status ?? 1,
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Weekend setting updated successfully!']);
    }

    public function destroy($id)
    {
        DB::table('weekend_settings')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Weekend setting deleted successfully!']);
    }
}
