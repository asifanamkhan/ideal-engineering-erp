<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceSettingController extends Controller
{
    public function index()
    {
        $setting = DB::table('invoice_settings')->first();
        return view('admin.settings.invoice', compact('setting'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = [
                'header_text' => $request->header_text,
                'footer_text' => $request->footer_text,
                'branch_id' => auth()->user()->branch_id ?? 1,
                'created_by' => auth()->id(),
                'updated_at' => now()
            ];

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = 'public/uploads/invoice-settings/logo_' . $filename;
                $file->move(public_path('uploads/invoice-settings'), 'logo_' . $filename);
                $data['logo'] = $path;
            }

            // Handle signature upload
            if ($request->hasFile('author_signature')) {
                $file = $request->file('author_signature');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $author_signature_path = 'public/uploads/invoice-settings/signature_' . $filename;
                $file->move(public_path('uploads/invoice-settings'), 'signature_' . $filename);
                $data['author_signature'] = $author_signature_path;
            }

            $existing = DB::table('invoice_settings')->first();

            if ($existing) {
                // Delete old logo if exists and new logo uploaded
                if ($request->hasFile('logo') && $existing->logo && file_exists(public_path($existing->logo))) {
                    unlink(base_path($existing->logo));
                }
                // Delete old signature if exists and new signature uploaded
                if ($request->hasFile('author_signature') && $existing->author_signature && file_exists(public_path($existing->author_signature))) {
                    unlink(base_path($existing->author_signature));
                }
                DB::table('invoice_settings')->where('id', $existing->id)->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('invoice_settings')->insert($data);
            }

            return redirect()->back()->with('success', 'Invoice settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
