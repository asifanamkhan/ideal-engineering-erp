<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsSettingsController extends Controller
{
    public function index()
    {
        // Get gateway settings
        $gateway = DB::table('sms_gateways')->first();

        // Get all templates - এখানে admin_sms_text সিলেক্ট করতে হবে
        $templates = [];
        $allTemplates = DB::table('sms_templates')
            ->select('module', 'sub_module', 'party_status', 'admin_status', 'sms_text', 'admin_sms_text')
            ->get();

        foreach ($allTemplates as $template) {
            $templates[$template->module][$template->sub_module] = [
                'party_status' => $template->party_status,
                'admin_status' => $template->admin_status,
                'sms_text' => $template->sms_text,
                'admin_sms_text' => $template->admin_sms_text  // ← এই লাইন যোগ করো
            ];
        }

        return view('admin.settings.sms.settings', compact('gateway', 'templates'));
    }

    public function update(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();

            // Update or create gateway settings
            DB::table('sms_gateways')->updateOrInsert(
                ['id' => 1],
                [
                    'api_url' => $request->api_url,
                    'api_key' => $request->api_key,
                    'api_secret' => $request->api_secret,
                    'sender_id' => $request->sender_id,
                    'admin_phone' => $request->admin_phone,
                    'status' => $request->gateway_status ? 1 : 0,
                    'updated_at' => now()
                ]
            );

            // Update templates
            $templates = $request->templates;
            foreach ($templates as $module => $items) {
                foreach ($items as $subModule => $data) {
                    DB::table('sms_templates')->updateOrInsert(
                        [
                            'module' => $module,
                            'sub_module' => $subModule
                        ],
                        [
                            'party_status' => isset($data['party_status']) ? 1 : 0,
                            'admin_status' => isset($data['admin_status']) ? 1 : 0,
                            'sms_text' => $data['sms_text'] ?? null,
                            'admin_sms_text' => $data['admin_sms_text'] ?? null,
                            'updated_at' => now()
                        ]
                    );
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'SMS settings updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}