<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class GenerelSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = DB::table('generel_settings')->first();
        return view('admin.settings.generel_settings.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = [
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'phone_two' => $request->phone_two ?? '',
            'address' => $request->address,
            'updated_at' => now(),
        ];

        if ($request->hasFile('logo')) {
            $imageName = time().'.'.$request->logo->extension();
            $request->logo->move(public_path('uploads/logo'), $imageName);
            $data['logo'] = 'public/uploads/logo/'.$imageName;
        }

        DB::table('generel_settings')->updateOrInsert(
            ['id' => 1],
            $data
        );

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
