<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentHelper
{
    private $drCrRules = [
        'job' => 'DR',
        'income' => 'DR',
        'sale' => 'DR',
        'purchase_return' => 'DR',
        'customer_payment' => 'DR',

        // CR Modules (Expense/Payable)
        'expense' => 'CR',      // ← এই লাইনটি যোগ করো
        'salary' => 'CR',
        'purchase' => 'CR',
        'sale_return' => 'CR',
    ];

    public function processPayment($paymentFor, $paymentForId, $type, $typeId, $paymentAmount, $paymentModeId, $additionalData = [], $entityData = [])
    {
        try {
            DB::beginTransaction();

            $paymentMode = DB::table('acc_payment_modes')->where('id', $paymentModeId)->first();
            if (!$paymentMode) {
                throw new \Exception('Payment mode not found');
            }

            $entityTable = $entityData['table'] ?? $type . 's';
            $amountField = $entityData['amount_field'] ?? 'amount';
            $discountField = $entityData['discount_field'] ?? 'discount';
            $paidField = $entityData['paid_field'] ?? 'paid_amount';
            $statusField = $entityData['status_field'] ?? 'payment_status';

            $entity = DB::table($entityTable)->where('id', $typeId)->first();
            if (!$entity) {
                throw new \Exception(ucfirst($type) . ' not found');
            }

            $totalAmount = $entity->$amountField - ($entity->$discountField ?? 0);
            $paidAmount = DB::table('payments')->where('type', $type)->where('type_id', $typeId)->sum('amount');
            $dueAmount = $totalAmount - $paidAmount;

            if ($paymentAmount > $dueAmount) {
                throw new \Exception('Payment amount cannot exceed due amount of ' . number_format($dueAmount, 2));
            }

            $drCr = $this->getDrCr($type);
            $remainingDue = $dueAmount - $paymentAmount;
            $difference = ($drCr == 'DR') ? $remainingDue : -$remainingDue;

            $paymentData = [
                'type' => $type,
                'type_id' => $typeId,
                'tran_type' => $type . '_payment',
                'tran_id' => $this->generateTransactionRefId($type),
                'amount' => $paymentAmount,
                'difference' => $difference,
                'dr_cr' => $drCr,
                'pay_method_id' => $paymentModeId,
                'payment_date' => now()->format('Y-m-d'),
                'payment_for' => $paymentFor,
                'payment_for_id' => $paymentForId,
                'narration' => $additionalData['narration'] ?? null,
                'created_by' => Auth::id(),
                'branch_id' => Auth::user()->branch_id ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $this->addPaymentMethodFields($paymentMode->mode_name, $additionalData, $paymentData);
            $paymentId = DB::table('payments')->insertGetId($paymentData);

            $newPaidAmount = $paidAmount + $paymentAmount;
            $paymentStatus = $this->getPaymentStatus($newPaidAmount, $totalAmount);

            DB::table($entityTable)->where('id', $typeId)->update([
                $paidField => $newPaidAmount,
                $statusField => $paymentStatus,
                'updated_at' => now()
            ]);

            DB::commit();

            // Send SMS notifications (after commit)
            try {
                // Prepare variables for SMS
                $variables = [
                    'payment_id' => $paymentId,
                    'tran_id' => $paymentData['tran_id'],
                    'amount' => number_format($paymentAmount, 2),
                    'payment_date' => now()->format('d-m-Y'),
                    'payment_mode' => $paymentMode->mode_name,
                    'due_amount' => number_format($dueAmount - $paymentAmount, 2),
                    'customer_name' => $this->getCustomerName($paymentFor, $paymentForId),
                    'job_id' => $type == 'job' ? $this->getJobId($typeId) : null,
                    'expense_no' => $type == 'expense' ? $this->getExpenseNo($typeId) : null,
                ];

                // Send SMS for payment
                if (class_exists('\App\Helpers\SmsHelper')) {
                    \App\Helpers\SmsHelper::sendToBoth('payment', $type . '_payment', $paymentId, $variables);
                }
            } catch (\Exception $e) {
                // Log SMS error but don't break payment process
                \Log::error('SMS sending failed: ' . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'Payment processed successfully!',
                'payment_id' => $paymentId,
                'payment_status' => $paymentStatus,
                'paid_amount' => number_format($newPaidAmount, 2),
                'due_amount' => number_format($totalAmount - $newPaidAmount, 2)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Add this helper method to get customer name
    private function getCustomerName($paymentFor, $paymentForId)
    {
        if ($paymentFor == 'customer' && $paymentForId) {
            $customer = DB::table('customers')->where('id', $paymentForId)->first();
            return $customer->name ?? 'N/A';
        }
        return 'N/A';
    }

    /**
     * Get expense number by expense ID
     */
    private function getExpenseNo($expenseId)
    {
        if (!$expenseId) {
            return null;
        }

        $expense = DB::table('expenses')->where('id', $expenseId)->first();
        return $expense->expense_no ?? null;
    }

    private function getJobId($jobBookId)
    {
        if (!$jobBookId) {
            return null;
        }

        $job = DB::table('job_books')->where('id', $jobBookId)->first();
        return $job->job_id ?? null;
    }

    public function updatePayment($paymentId, $paymentAmount, $paymentModeId, $additionalData = [], $entityData = [])
    {
        try {
            DB::beginTransaction();

            $oldPayment = DB::table('payments')->where('id', $paymentId)->first();
            if (!$oldPayment) {
                throw new \Exception('Payment not found');
            }

            $paymentMode = DB::table('acc_payment_modes')->where('id', $paymentModeId)->first();
            if (!$paymentMode) {
                throw new \Exception('Payment mode not found');
            }

            // Update payment
            $paymentData = [
                'amount' => $paymentAmount,
                'pay_method_id' => $paymentModeId,
                'narration' => $additionalData['narration'] ?? null,
                'updated_at' => now()
            ];

            $this->addPaymentMethodFields($paymentMode->mode_name, $additionalData, $paymentData);
            DB::table('payments')->where('id', $paymentId)->update($paymentData);

            // Recalculate total paid amount (শুধু যোগ করলেই হবে না, পুরনো ভ্যালু বাদ দিয়ে নতুন যোগ করতে হবে)
            $type = $oldPayment->type;
            $typeId = $oldPayment->type_id;

            // পুরো paid_amount রি-ক্যালকুলেট করো
            $newPaidAmount = DB::table('payments')->where('type', $type)->where('type_id', $typeId)->sum('amount');

            // Get entity table info
            $entityTable = $entityData['table'] ?? $type . 's';
            $amountField = $entityData['amount_field'] ?? 'amount';
            $discountField = $entityData['discount_field'] ?? 'discount';
            $paidField = $entityData['paid_field'] ?? 'paid_amount';
            $statusField = $entityData['status_field'] ?? 'payment_status';

            $entity = DB::table($entityTable)->where('id', $typeId)->first();
            $totalAmount = $entity->$amountField - ($entity->$discountField ?? 0);

            // স্ট্যাটাস নির্ধারণ
            $paymentStatus = 'unpaid';
            if ($newPaidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
            } elseif ($newPaidAmount > 0) {
                $paymentStatus = 'partial_paid';
            }

            // Update entity table
            DB::table($entityTable)->where('id', $typeId)->update([
                $paidField => $newPaidAmount,
                $statusField => $paymentStatus,
                'updated_at' => now()
            ]);

            DB::commit();

            return ['success' => true, 'message' => 'Payment updated successfully!'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deletePayment($paymentId, $typeId, $entityData = [])
    {
        try {
            DB::beginTransaction();

            $payment = DB::table('payments')->where('id', $paymentId)->first();
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            $type = $payment->type;

            DB::table('payments')->where('id', $paymentId)->delete();

            $newPaidAmount = DB::table('payments')->where('type', $type)->where('type_id', $typeId)->sum('amount');

            // Get entity table info from entityData or dynamic
            $entityTable = $entityData['table'] ?? $type . 's';
            $amountField = $entityData['amount_field'] ?? 'amount';
            $discountField = $entityData['discount_field'] ?? 'discount';
            $paidField = $entityData['paid_field'] ?? 'paid_amount';
            $statusField = $entityData['status_field'] ?? 'payment_status';

            $entity = DB::table($entityTable)->where('id', $typeId)->first();
            $totalAmount = $entity->$amountField - ($entity->$discountField ?? 0);
            $paymentStatus = $this->getPaymentStatus($newPaidAmount, $totalAmount);

            DB::table($entityTable)->where('id', $typeId)->update([
                $paidField => $newPaidAmount,
                $statusField => $paymentStatus,
                'updated_at' => now()
            ]);

            DB::commit();

            return ['success' => true, 'message' => 'Payment deleted successfully!'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getPaymentDetails($type, $typeId, $entityData = [])
    {
        try {
            $entityTable = $entityData['table'] ?? $type . 's';
            $amountField = $entityData['amount_field'] ?? 'amount';
            $discountField = $entityData['discount_field'] ?? null;  // null by default
            $paidField = $entityData['paid_field'] ?? 'paid_amount';

            // সিলেক্ট করার জন্য ফিল্ড তৈরি
            $selectFields = ['id', $amountField, $paidField];

            // discount_field দেওয়া থাকলে এবং টেবিলে কলাম থাকলে তবেই যোগ করো
            if ($discountField) {
                // চেক করো কলাম টেবিলে আছে কিনা
                $columns = DB::getSchemaBuilder()->getColumnListing($entityTable);
                if (in_array($discountField, $columns)) {
                    $selectFields[] = $discountField;
                } else {
                    $discountField = null; // কলাম না থাকলে null করে দাও
                }
            }

            $entity = DB::table($entityTable)
                ->select($selectFields)
                ->where('id', $typeId)
                ->first();

            if (!$entity) {
                return ['success' => false, 'message' => ucfirst($type) . ' not found'];
            }

            // discount থাকলে বিয়োগ করো, না থাকলে 0
            $discount = 0;
            if ($discountField && property_exists($entity, $discountField)) {
                $discount = $entity->$discountField ?? 0;
            }

            $totalAmount = $entity->$amountField - $discount;
            $paidAmount = DB::table('payments')->where('type', $type)->where('type_id', $typeId)->sum('amount');

            if ($paidAmount == 0) {
                $paidAmount = $entity->$paidField ?? 0;
            }

            $dueAmount = $totalAmount - $paidAmount;

            return [
                'success' => true,
                'total_amount' => number_format($totalAmount, 2),
                'paid_amount' => number_format($paidAmount, 2),
                'due_amount' => number_format($dueAmount, 2),
                'raw_total' => $totalAmount,
                'raw_paid' => $paidAmount,
                'raw_due' => $dueAmount
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getPaymentHistory($type, $typeId)
    {
        try {
            $payments = DB::table('payments')
                ->leftJoin('acc_payment_modes', 'payments.pay_method_id', '=', 'acc_payment_modes.id')
                ->select('payments.*', 'acc_payment_modes.mode_name as payment_mode')
                ->where('payments.type', $type)
                ->where('payments.type_id', $typeId)
                ->orderBy('payments.id', 'desc')
                ->get();

            return ['success' => true, 'data' => $payments];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getSinglePayment($paymentId)
    {
        try {
            $payment = DB::table('payments')->where('id', $paymentId)->first();
            if (!$payment) {
                return ['success' => false, 'message' => 'Payment not found'];
            }
            return ['success' => true, 'data' => $payment];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function addPaymentMethodFields($modeName, $additionalData, &$paymentData)
    {
        switch ($modeName) {
            case 'Cheque':
                if (isset($additionalData['chq_no'])) $paymentData['chq_no'] = $additionalData['chq_no'];
                if (isset($additionalData['chq_date'])) $paymentData['chq_date'] = $additionalData['chq_date'];
                break;
            case 'Card':
                if (isset($additionalData['card_no'])) $paymentData['card_no'] = $additionalData['card_no'];
                if (isset($additionalData['online_trx_id'])) $paymentData['online_trx_id'] = $additionalData['online_trx_id'];
                if (isset($additionalData['online_trx_dt'])) $paymentData['online_trx_dt'] = $additionalData['online_trx_dt'];
                break;
            case 'Mobile Banking':
                if (isset($additionalData['mfs_name'])) $paymentData['mfs_name'] = $additionalData['mfs_name'];
                if (isset($additionalData['online_trx_id'])) $paymentData['online_trx_id'] = $additionalData['online_trx_id'];
                if (isset($additionalData['online_trx_dt'])) $paymentData['online_trx_dt'] = $additionalData['online_trx_dt'];
                break;
            case 'Internet Banking':
                if (isset($additionalData['bank_code'])) $paymentData['bank_code'] = $additionalData['bank_code'];
                if (isset($additionalData['bank_ac_no'])) $paymentData['bank_ac_no'] = $additionalData['bank_ac_no'];
                if (isset($additionalData['online_trx_id'])) $paymentData['online_trx_id'] = $additionalData['online_trx_id'];
                if (isset($additionalData['online_trx_dt'])) $paymentData['online_trx_dt'] = $additionalData['online_trx_dt'];
                break;
        }
    }

    private function getDrCr($type)
    {
        return $this->drCrRules[$type] ?? 'DR';
    }

    private function getPaymentStatus($paidAmount, $totalAmount)
    {
        if ($paidAmount >= $totalAmount) return 'paid';
        elseif ($paidAmount > 0 && $paidAmount < $totalAmount) return 'partial_paid';
        else return 'unpaid';
    }


    private function generateTransactionRefId($type)
    {
        $prefix = match ($type) {
            'job' => 'JOB-',
            'expense' => 'EXP-',
            'customer_payment' => 'PAY-',
            'income' => 'INC-',
            'salary' => 'SAL-',
            'purchase' => 'PUR-',
            default => 'TRX-'
        };

        // Last 6 digits of timestamp
        $time = substr(time(), -6);

        // Random 3 digits
        $random = rand(100, 999);

        return $prefix . $time . $random;
    }
}