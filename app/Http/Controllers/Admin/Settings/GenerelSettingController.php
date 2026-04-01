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
        return view('admin.settings.generel_settings.edit');
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

        $generel_settings = DB::table('generel_settings')->where('id', 1)->first();
        // Handle photo upload
        $photoPath = @$generel_settings->logo ?? '';

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            try {
                // Delete old photo if exists
                if ($request->photo && file_exists(public_path($request->photo))) {
                    unlink(public_path($request->photo));
                }

                $photoFile = $request->file('logo');
                $filename = time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'public/uploads/logo/' . $filename;
                $photoFile->move(public_path('uploads/logo'), $filename);
            } catch (\Exception $e) {
                $photoPath = $request->logo; // Keep old photo on error
            }
        }

        $data['logo'] = $photoPath;

        DB::table('generel_settings')->updateOrInsert(
            ['id' => 1],
            $data
        );

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
