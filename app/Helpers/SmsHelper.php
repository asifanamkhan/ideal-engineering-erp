<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsHelper
{
    /**
     * Send SMS using configured gateway
     *
     * @param string|array $phones (single phone or array of phones)
     * @param string $message
     * @param string $campaignTitle
     * @return array
     */
    public static function send($phones, $message, $campaignTitle = 'Ideal Engineering')
    {
        try {
            // Get gateway settings
            $gateway = DB::table('sms_gateways')->first();

            if (!$gateway || $gateway->status != 1) {
                Log::warning('SMS gateway is not configured or inactive');
                return ['success' => false, 'message' => 'SMS gateway inactive'];
            }

            // Convert phones to comma-separated string
            if (is_array($phones)) {
                $phoneString = implode(',', array_map([self::class, 'cleanPhoneNumber'], $phones));
            } else {
                $phoneString = self::cleanPhoneNumber($phones);
            }

            if (empty($phoneString)) {
                return ['success' => false, 'message' => 'Phone number is required'];
            }

            // Prepare message
            $message = self::truncateMessage($message);

            // Detect if message contains Unicode characters
            $isUnicode = self::isUnicode($message);
            $messageType = $isUnicode ? 'UNICODE' : 'TEXT';

            // Prepare API parameters
            $postData = [
                'api_key' => $gateway->api_key,
                'api_secret' => $gateway->api_secret,
                'request_type' => is_array($phones) ? 'GENERAL_CAMPAIGN' : 'SINGLE_SMS',
                'message_type' => $messageType,
                'mobile' => $phoneString,
                'message_body' => $message,
                'campaign_title' => $campaignTitle
            ];

            // Send via Laravel HTTP Client
            $response = self::sendViaHttp($gateway->api_url, $postData);

            // Log SMS for debugging
            Log::info('SMS Sent', [
                'phones' => $phoneString,
                'message' => $message,
                'response' => $response
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send SMS to multiple recipients
     *
     * @param array $phones
     * @param string $message
     * @param string $campaignTitle
     * @return array
     */
    public static function sendBulk($phones, $message, $campaignTitle = 'Ideal Engineering')
    {
        return self::send($phones, $message, $campaignTitle);
    }

    /**
     * Send SMS to Party (Customer/Supplier)
     *
     * @param string $module (job, payment, expense)
     * @param string $subModule (job_create, job_payment, expense_create)
     * @param int $typeId
     * @param array $variables
     * @return bool
     */
    public static function sendToParty($module, $subModule, $typeId, $variables = [])
    {
        try {
            // Get template
            $template = DB::table('sms_templates')
                ->where('module', $module)
                ->where('sub_module', $subModule)
                ->first();

            // Check if party SMS is enabled
            if (!$template || $template->party_status != 1 || empty($template->sms_text)) {
                return false;
            }

            // Get phone number based on module
            $phone = self::getPartyPhone($module, $typeId);

            if (empty($phone)) {
                return false;
            }

            // Replace variables
            $message = self::replaceVariables($template->sms_text, $variables);

            // Send SMS
            $result = self::send($phone, $message, ucfirst($module) . ' Notification');

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Party SMS failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS to Admin
     *
     * @param string $module (job, payment, expense)
     * @param string $subModule (job_create, job_payment, expense_create)
     * @param array $variables
     * @return bool
     */
    public static function sendToAdmin($module, $subModule, $variables = [])
    {
        try {
            // Get gateway for admin phone
            $gateway = DB::table('sms_gateways')->first();

            if (!$gateway || empty($gateway->admin_phone)) {
                return false;
            }

            // Get template
            $template = DB::table('sms_templates')
                ->where('module', $module)
                ->where('sub_module', $subModule)
                ->first();

            // Check if admin SMS is enabled
            if (!$template || $template->admin_status != 1 || empty($template->admin_sms_text)) {
                return false;
            }

            // Replace variables
            $message = self::replaceVariables($template->admin_sms_text, $variables);

            // Send SMS to admin
            $result = self::send($gateway->admin_phone, $message, 'Admin Notification');

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Admin SMS failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS to both Party and Admin
     *
     * @param string $module
     * @param string $subModule
     * @param int $typeId
     * @param array $variables
     * @return array
     */
    public static function sendToBoth($module, $subModule, $typeId, $variables = [])
    {
        $results = [
            'party' => self::sendToParty($module, $subModule, $typeId, $variables),
            'admin' => self::sendToAdmin($module, $subModule, $variables)
        ];

        return $results;
    }

    /**
     * Get party phone number based on module
     */
    private static function getPartyPhone($module, $typeId)
    {
        switch ($module) {
            case 'job':
                $job = DB::table('job_books')->where('id', $typeId)->first();
                if ($job && $job->customer_id) {
                    $customer = DB::table('customers')->where('id', $job->customer_id)->first();
                    return $customer->phone ?? null;
                }
                break;

            case 'payment':
                $payment = DB::table('payments')->where('id', $typeId)->first();
                if ($payment && $payment->payment_for == 'customer' && $payment->payment_for_id) {
                    $customer = DB::table('customers')->where('id', $payment->payment_for_id)->first();
                    return $customer->phone ?? null;
                }
                break;

            case 'expense':
                return null;
        }

        return null;
    }

    /**
     * Replace variables in template
     */
    private static function replaceVariables($template, $variables)
    {
        $search = [];
        $replace = [];

        foreach ($variables as $key => $value) {
            $search[] = '{' . $key . '}';
            $replace[] = $value ?? '';
        }
        $result = str_replace($search, $replace, $template);

        \Log::info('replaceVariables result: ' . $result);

        return $result;

    }

    /**
     * Clean phone number
     */
    private static function cleanPhoneNumber($phone)
    {
        if (empty($phone)) return '';

        // Remove spaces, dashes, plus sign
        $phone = preg_replace('/[\s\-]/', '', $phone);
        $phone = ltrim($phone, '+');

        // For Bangladesh numbers
        if (strlen($phone) == 11 && substr($phone, 0, 2) == '01') {
            $phone = '88' . $phone;
        } elseif (strlen($phone) == 13 && substr($phone, 0, 3) == '880') {
            $phone = $phone;
        }

        return $phone;
    }

    /**
     * Check if message contains Unicode characters
     */
    private static function isUnicode($message)
    {
        return preg_match('/[^\x00-\x7F]/', $message) > 0;
    }

    /**
     * Truncate message
     */
    private static function truncateMessage($message)
    {
        $maxLength = self::isUnicode($message) ? 70 : 160;

        if (strlen($message) > $maxLength) {
            return substr($message, 0, $maxLength - 3) . '...';
        }
        return $message;
    }

    /**
     * Send SMS via Laravel HTTP Client
     */
    private static function sendViaHttp($url, $postData)
    {
        try {
            $response = Http::asForm()->post($url, $postData);

            $responseData = $response->json();
            $statusCode = $response->status();

            if ($responseData && isset($responseData['api_response_code']) && $responseData['api_response_code'] == 200) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'campaign_uid' => $responseData['campaign_uid'] ?? null,
                    'invalid_numbers' => $responseData['invalid_numbers'] ?? []
                ];
            }

            $errorMessage = $responseData['api_response_message'] ?? 'Failed to send SMS';
            $errorDetail = $responseData['error']['error_message'] ?? '';

            return [
                'success' => false,
                'message' => $errorMessage . ($errorDetail ? ': ' . $errorDetail : ''),
                'response' => $responseData
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'HTTP Error: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Send SMS for any event (Job Create, Expense Create, etc.)
     *
     * @param string $module (job, expense, payment)
     * @param string $event (job_create, expense_create, invoice_create, etc.)
     * @param int $typeId (job_id, expense_id, payment_id)
     * @param array $extraVariables (optional extra variables)
     * @return array
     */
    public static function sendForEvent($module, $event, $typeId, $extraVariables = [])
    {
        \Log::info('sendForEvent called with:', [
            'module' => $module,
            'event' => $event,
            'typeId' => $typeId,
            'extraVariables' => $extraVariables
        ]);
        try {
            // Get template
            $template = DB::table('sms_templates')
                ->where('module', $module)
                ->where('sub_module', $event)
                ->first();
            \Log::info('Template found: ' . ($template ? 'Yes' : 'No'));
            if (!$template) {
                return ['success' => false, 'message' => 'Template not found'];
            }

            // Prepare variables based on module
            $variables = self::getVariablesByModule($module, $typeId);

            // Merge extra variables
            $variables = array_merge($variables, $extraVariables);

            $results = [];

            // Send to Party (if enabled)
            if ($template->party_status == 1 && !empty($template->sms_text)) {
                $phone = self::getPartyPhone($module, $typeId);
                if ($phone) {
                    $message = self::replaceVariables($template->sms_text, $variables);
                    $results['party'] = self::send($phone, $message, ucfirst($module) . ' Notification');
                }
            }

            // Send to Admin (if enabled)
            if ($template->admin_status == 1 && !empty($template->admin_sms_text)) {
                $gateway = DB::table('sms_gateways')->first();
                if ($gateway && !empty($gateway->admin_phone)) {
                    $message = self::replaceVariables($template->admin_sms_text, $variables);
                    $results['admin'] = self::send($gateway->admin_phone, $message, 'Admin Notification');
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('SMS send failed for event: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get variables based on module and type_id
     */
    private static function getVariablesByModule($module, $typeId)
    {
        $variables = [];

        switch ($module) {
            case 'job':
                $job = DB::table('job_books')->where('id', $typeId)->first();
                if ($job) {
                    $customer = DB::table('customers')->where('id', $job->customer_id)->first();
                    \Log::info('Customer data: ', (array)$customer);
                    $variables = [
                        'job_id' => $job->job_id,
                        'customer_name' => $customer->name ?? 'N/A',
                        'customer_phone' => $customer->phone ?? 'N/A',
                        'job_date' => $job->job_date,
                        'engine' => $job->engine ?? 'N/A',  // ← Already added
                        'vehicle_registration_no' => $job->vehicle_registration_no,  // ← Already added
                        'total_amount' => number_format($job->invoice_amount ?? 0, 2),
                        'invoice_paid_amount' => number_format($job->invoice_paid_amount ?? 0, 2),
                        'due_amount' => number_format(($job->invoice_amount ?? 0) - ($job->invoice_paid_amount ?? 0), 2),
                        'status' => $job->job_status ?? 'pending',
                    ];
                }
                break;

            case 'expense':
                $expense = DB::table('expenses')->where('id', $typeId)->first();
                if ($expense) {
                    $expenseDetails = DB::table('expense_details')
                        ->leftJoin('expense_categories', 'expense_details.expense_category_id', '=', 'expense_categories.id')
                        ->where('expense_details.expense_id', $typeId)
                        ->first();

                    $variables = [
                        'expense_no' => $expense->expense_no,
                        'amount' => number_format($expense->total_amount, 2),
                        'date' => $expense->date,
                        'category' => $expenseDetails->name ?? 'N/A',
                        'narration' => $expense->narration ?? '',
                        'status' => $expense->status == 1 ? 'Active' : 'Inactive',
                    ];
                }
                break;

            case 'salary':
                $salary = DB::table('salaries')->where('id', $typeId)->first();
                if ($salary) {
                    $employee = DB::table('employees')->where('id', $salary->employee_id)->first();
                    $variables = [
                        'employee_name' => $employee->name ?? 'N/A',
                        'employee_id' => $employee->employee_id ?? 'N/A',
                        'amount' => number_format($salary->amount, 2),
                        'salary_month' => $salary->salary_month,
                        'payment_date' => $salary->payment_date,
                        'department' => $employee->department ?? 'N/A',
                        'designation' => $employee->designation ?? 'N/A',
                    ];
                }
                break;

            case 'purchase':
                $purchase = DB::table('purchases')->where('id', $typeId)->first();
                if ($purchase) {
                    $supplier = DB::table('suppliers')->where('id', $purchase->supplier_id)->first();
                    $variables = [
                        'purchase_no' => $purchase->purchase_no,
                        'supplier_name' => $supplier->name ?? 'N/A',
                        'supplier_phone' => $supplier->phone ?? 'N/A',
                        'amount' => number_format($purchase->total_amount, 2),
                        'date' => $purchase->date,
                        'status' => $purchase->status == 1 ? 'Active' : 'Inactive',
                    ];
                }
                break;

            case 'income':
                $income = DB::table('incomes')->where('id', $typeId)->first();
                if ($income) {
                    $variables = [
                        'income_no' => $income->income_no,
                        'amount' => number_format($income->amount, 2),
                        'date' => $income->date,
                        'category' => $income->category,
                        'narration' => $income->narration ?? '',
                    ];
                }
                break;

            case 'customer':
                $customer = DB::table('customers')->where('id', $typeId)->first();
                if ($customer) {
                    $variables = [
                        'customer_name' => $customer->name ?? 'N/A',
                        'customer_phone' => $customer->phone ?? 'N/A',
                        'customer_email' => $customer->email ?? 'N/A',
                        'customer_address' => $customer->address ?? 'N/A',
                    ];
                }
                break;

            case 'supplier':
                $supplier = DB::table('suppliers')->where('id', $typeId)->first();
                if ($supplier) {
                    $variables = [
                        'supplier_name' => $supplier->name ?? 'N/A',
                        'supplier_phone' => $supplier->phone ?? 'N/A',
                        'supplier_email' => $supplier->email ?? 'N/A',
                        'supplier_address' => $supplier->address ?? 'N/A',
                    ];
                }
                break;
        }

        return $variables;
    }
}