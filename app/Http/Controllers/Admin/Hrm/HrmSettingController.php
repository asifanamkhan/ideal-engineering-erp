<?php
// app/Http/Controllers/Admin/Hrm/HrmSettingController.php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HrmSettingController extends Controller
{
    public function index()
    {
        $setting = DB::table('hrm_settings')->first();
        return view('admin.hrm.settings.index', compact('setting'));
    }

    // Add to HrmSettingController update method
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_check_in' => 'required',
            'default_check_out' => 'required',
            'default_overtime_hour' => 'required|numeric|min:0|max:24',
            'late_grace_minutes' => 'required|numeric|min:0|max:120',
            'early_grace_minutes' => 'required|numeric|min:0|max:120',
            'working_hours_per_day' => 'required|numeric|min:1|max:24',
            'late_deduction_enabled' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // dd($request->input());
        DB::table('hrm_settings')->update([
            'default_check_in' => $request->default_check_in,
            'default_check_out' => $request->default_check_out,
            'default_overtime_hour' => $request->default_overtime_hour,
            'late_grace_minutes' => $request->late_grace_minutes,
            'early_grace_minutes' => $request->early_grace_minutes,
            'working_hours_per_day' => $request->working_hours_per_day,
            'late_hours_for_full_day_deduction' => $request->late_hours_for_full_day_deduction, // ✅ Add this
            'late_deduction_enabled' => $request->late_deduction_enabled,
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Settings updated successfully!']);
    }
}
