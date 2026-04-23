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

        return str_replace($search, $replace, $template);
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
}