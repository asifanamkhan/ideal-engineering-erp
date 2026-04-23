<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\PaymentHelper;

class PaymentController extends Controller
{
    protected $paymentHelper;

    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $payments = DB::table('payments')
                ->leftJoin('acc_payment_modes', 'payments.pay_method_id', '=', 'acc_payment_modes.id')
                ->select(
                    'payments.*',
                    'acc_payment_modes.mode_name as payment_mode'
                )
                ->orderBy('payments.id', 'desc');

            return DataTables::of($payments)
                ->addIndexColumn()
                ->addColumn('payment_for_display', function ($payment) {
                    $for = ucfirst($payment->payment_for ?? 'N/A');
                    if ($payment->payment_for_id) {
                        if ($payment->payment_for == 'customer') {
                            $customer = DB::table('customers')->where('id', $payment->payment_for_id)->first();
                            $for .= ': ' . ($customer->name ?? 'N/A');
                        } elseif ($payment->payment_for == 'supplier') {
                            $supplier = DB::table('suppliers')->where('id', $payment->payment_for_id)->first();
                            $for .= ': ' . ($supplier->name ?? 'N/A');
                        }
                    }
                    return $for;
                })
                ->addColumn('type_display', function ($payment) {
                    $type = ucfirst($payment->type ?? 'N/A');
                    if ($payment->type_id) {
                        if ($payment->type == 'job') {
                            $job = DB::table('job_books')->where('id', $payment->type_id)->first();
                            $type .= ': ' . ($job->job_id ?? 'N/A');
                        } elseif ($payment->type == 'expense') {
                            $expense = DB::table('expenses')->where('id', $payment->type_id)->first();
                            $type .= ': ' . ($expense->expense_no ?? 'N/A');
                        }
                    }
                    return $type;
                })
                ->addColumn('amount_display', function ($payment) {
                    $sign = ($payment->dr_cr == 'DR') ? '+' : '-';
                    return '<span class="' . ($payment->dr_cr == 'DR' ? 'text-success' : 'text-danger') . '">' . $sign . ' ৳ ' . number_format($payment->amount, 2) . '</span>';
                })
                ->addColumn('date', function ($payment) {
                    return date('d-m-Y', strtotime($payment->payment_date));
                })
                ->addColumn('action', function ($payment) {
                    return view('admin.payments.partials.action-btn-view', ['id' => $payment->id])->render();
                })
                ->rawColumns(['amount_display', 'action'])
                ->make(true);
        }

        $paymentModes = DB::table('acc_payment_modes')->get();
        $bankInfos = DB::table('acc_bank_info')->get();
        return view('admin.payments.index', compact('paymentModes', 'bankInfos'));
    }

    public function create()
    {
        $paymentModes = DB::table('acc_payment_modes')->get();
        $bankInfos = DB::table('acc_bank_info')->get();
        return view('admin.payments.create', compact('paymentModes', 'bankInfos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date',
            'payment_for' => 'required|in:customer,supplier',
            'payment_for_id' => 'nullable|integer',
            'type' => 'required|in:job,expense,income,salary,purchase',
            'type_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode_id' => 'required|exists:acc_payment_modes,id',
            'dr_cr' => 'required|in:DR,CR',
            'narration' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $paymentMode = DB::table('acc_payment_modes')->where('id', $request->payment_mode_id)->first();

            $paymentData = [
                'type' => $request->type,
                'type_id' => $request->type_id,
                'tran_type' => 'manual_payment',
                'tran_id' => $this->generateTransactionId(),
                'amount' => $request->amount,
                'difference' => 0,
                'dr_cr' => $request->dr_cr,
                'pay_method_id' => $request->payment_mode_id,
                'payment_date' => $request->payment_date,
                'payment_for' => $request->payment_for,
                'payment_for_id' => $request->payment_for_id,
                'narration' => $request->narration,
                'created_by' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Add payment method specific fields
            if ($request->chq_no) $paymentData['chq_no'] = $request->chq_no;
            if ($request->chq_date) $paymentData['chq_date'] = $request->chq_date;
            if ($request->card_no) $paymentData['card_no'] = $request->card_no;
            if ($request->online_trx_id) $paymentData['online_trx_id'] = $request->online_trx_id;
            if ($request->online_trx_dt) $paymentData['online_trx_dt'] = $request->online_trx_dt;
            if ($request->mfs_name) $paymentData['mfs_name'] = $request->mfs_name;
            if ($request->bank_code) $paymentData['bank_code'] = $request->bank_code;
            if ($request->bank_ac_no) $paymentData['bank_ac_no'] = $request->bank_ac_no;

            DB::table('payments')->insert($paymentData);

            DB::commit();

            return redirect()->route('admin.payments.index')
                ->with('success', 'Payment created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $payment = DB::table('payments')
            ->leftJoin('acc_payment_modes', 'payments.pay_method_id', '=', 'acc_payment_modes.id')
            ->select('payments.*', 'acc_payment_modes.mode_name as payment_mode')
            ->where('payments.id', $id)
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $payment]);
    }

    public function edit($id)
    {
        $payment = DB::table('payments')->where('id', $id)->first();
        if (!$payment) {
            return redirect()->route('admin.payments.index')->with('error', 'Payment not found');
        }

        $paymentModes = DB::table('acc_payment_modes')->get();
        $bankInfos = DB::table('acc_bank_info')->get();

        return view('admin.payments.edit', compact('payment', 'paymentModes', 'bankInfos'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode_id' => 'required|exists:acc_payment_modes,id',
            'narration' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $payment = DB::table('payments')->where('id', $id)->first();
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // পেমেন্টের টাইপ অনুযায়ী entityData গতিশীলভাবে তৈরি
            $entityData = $this->getEntityDataByType($payment->type);

            $additionalData = $request->only([
                'chq_no',
                'chq_date',
                'card_no',
                'online_trx_id',
                'online_trx_dt',
                'mfs_name',
                'bank_code',
                'bank_ac_no',
                'narration'
            ]);

            $result = $this->paymentHelper->updatePayment(
                $id,
                $request->amount,
                $request->payment_mode_id,
                $additionalData,
                $entityData
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getEntityDataByType($type)
    {
        $entityMap = [
            'job' => [
                'table' => 'job_books',
                'amount_field' => 'invoice_amount',
                'discount_field' => 'invoice_discount',
                'paid_field' => 'invoice_paid_amount',
                'status_field' => 'invoice_status'
            ],
            'expense' => [
                'table' => 'expenses',
                'amount_field' => 'total_amount',
                'discount_field' => 'discount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
            ],
            'salary' => [
                'table' => 'salaries',
                'amount_field' => 'amount',
                'discount_field' => 'discount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
            ],
            'purchase' => [
                'table' => 'purchases',
                'amount_field' => 'total_amount',
                'discount_field' => 'discount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
            ],
            'customer_payment' => [
                'table' => 'customers',
                'amount_field' => 'total_due',
                'discount_field' => 'discount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
            ],
        ];

        return $entityMap[$type] ?? [
            'table' => $type . 's',
            'amount_field' => 'amount',
            'discount_field' => 'discount',
            'paid_field' => 'paid_amount',
            'status_field' => 'payment_status'
        ];
    }

    public function destroy($id)
    {
        try {
            DB::table('payments')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Payment deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete payment'], 500);
        }
    }

    private function generateTransactionId()
    {
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        $sequence = DB::table('payments')->whereDate('created_at', today())->count() + 1;
        return 'PAY-' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT) . $random;
    }

    public function customerPaymentCreate()
    {
        $paymentModes = DB::table('acc_payment_modes')->get();
        $bankInfos = DB::table('acc_bank_info')->get();
        return view('admin.payments.customer-payment', compact('paymentModes', 'bankInfos'));
    }

    // Get customer jobs for payment
    public function getCustomerJobs(Request $request)
    {
        $customerId = $request->customer_id;

        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Customer ID required']);
        }

        // Get all jobs of this customer with payment details
        $jobs = DB::table('job_books')
            ->where('customer_id', $customerId)
            ->whereIn('invoice_status', ['unpaid', 'partial_paid'])
            ->orderBy('job_date', 'asc')
            ->select(
                'id',
                'job_id',
                'job_date',
                'engine',
                'invoice_amount',
                'invoice_discount',
                'invoice_paid_amount',
                'invoice_status'
            )
            ->get();

        foreach ($jobs as $job) {
            $totalAmount = $job->invoice_amount - ($job->invoice_discount ?? 0);
            $paidAmount = $job->invoice_paid_amount ?? 0;
            $dueAmount = $totalAmount - $paidAmount;

            $job->total_amount = $totalAmount;
            $job->paid_amount = $paidAmount;
            $job->due_amount = $dueAmount;
        }

        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    }

    // Store customer payment
    public function storeCustomerPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:random,job',
            'payment_amount' => 'required_if:payment_type,random|numeric|min:0.01',
            'payment_mode_id' => 'required|exists:acc_payment_modes,id',
            'jobs' => 'required_if:payment_type,job|nullable|json',  // JSON as expect
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->payment_type == 'random') {
                // Skip due amount validation for random payment
                $additionalData = $this->getPaymentAdditionalData($request);

                // Direct payment insert without paymentHelper validation
                $paymentMode = DB::table('acc_payment_modes')->where('id', $request->payment_mode_id)->first();

                $paymentData = [
                    'type' => 'customer_payment',
                    'type_id' => $request->customer_id,
                    'tran_type' => 'customer_random_payment',
                    'tran_id' => $this->generateTransactionId(),
                    'amount' => $request->payment_amount,
                    'difference' => 0,
                    'dr_cr' => 'DR',
                    'pay_method_id' => $request->payment_mode_id,
                    'payment_date' => $request->payment_date,
                    'payment_for' => 'customer',
                    'payment_for_id' => $request->customer_id,
                    'narration' => $request->narration,
                    'created_by' => auth()->id(),
                    'branch_id' => auth()->user()->branch_id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Add payment method specific fields
                if ($request->chq_no) $paymentData['chq_no'] = $request->chq_no;
                if ($request->chq_date) $paymentData['chq_date'] = $request->chq_date;
                if ($request->card_no) $paymentData['card_no'] = $request->card_no;
                if ($request->online_trx_id) $paymentData['online_trx_id'] = $request->online_trx_id;
                if ($request->online_trx_dt) $paymentData['online_trx_dt'] = $request->online_trx_dt;
                if ($request->mfs_name) $paymentData['mfs_name'] = $request->mfs_name;
                if ($request->bank_code) $paymentData['bank_code'] = $request->bank_code;
                if ($request->bank_ac_no) $paymentData['bank_ac_no'] = $request->bank_ac_no;

                DB::table('payments')->insert($paymentData);

                // Update customer paid amount
                $customer = DB::table('customers')->where('id', $request->customer_id)->first();
                $newPaidAmount = ($customer->paid_amount ?? 0) + $request->payment_amount;

                DB::table('customers')->where('id', $request->customer_id)->update([
                    'paid_amount' => $newPaidAmount,
                    'updated_at' => now()
                ]);
            } else {
                // Job wise payments - Parse JSON to array
                $jobs = json_decode($request->jobs, true);

                if (!is_array($jobs) || empty($jobs)) {
                    throw new \Exception('No payment data found');
                }

                $totalPaymentAmount = 0;

                foreach ($jobs as $jobData) {
                    if ($jobData['amount'] > 0) {
                        $totalPaymentAmount += $jobData['amount'];

                        $additionalData = $this->getPaymentAdditionalData($request);

                        $entityData = [
                            'table' => 'job_books',
                            'amount_field' => 'invoice_amount',
                            'discount_field' => 'invoice_discount',
                            'paid_field' => 'invoice_paid_amount',
                            'status_field' => 'invoice_status'
                        ];

                        $result = $this->paymentHelper->processPayment(
                            'customer',
                            $request->customer_id,
                            'job',
                            $jobData['job_id'],
                            $jobData['amount'],
                            $request->payment_mode_id,
                            $additionalData,
                            $entityData
                        );

                        if (!$result['success']) {
                            throw new \Exception($result['message']);
                        }
                    }
                }

                if ($totalPaymentAmount == 0) {
                    throw new \Exception('No payment amount entered');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getPaymentAdditionalData($request)
    {
        return [
            'narration' => $request->narration,
            'chq_no' => $request->chq_no,
            'chq_date' => $request->chq_date,
            'card_no' => $request->card_no,
            'online_trx_id' => $request->online_trx_id,
            'online_trx_dt' => $request->online_trx_dt,
            'mfs_name' => $request->mfs_name,
            'bank_code' => $request->bank_code,
            'bank_ac_no' => $request->bank_ac_no,
        ];
    }

    public function printReceipt($id)
    {
        try {
            $payment = DB::table('payments')
                ->leftJoin('acc_payment_modes', 'payments.pay_method_id', '=', 'acc_payment_modes.id')
                ->select('payments.*', 'acc_payment_modes.mode_name as payment_mode')
                ->where('payments.id', $id)
                ->first();

            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // Get customer name if exists
            $relatedData = null;
            if ($payment->payment_for_id && $payment->payment_for == 'customer') {
                $customer = DB::table('customers')->where('id', $payment->payment_for_id)->first();
                $relatedData = (object)[
                    'customer_name' => $customer->name ?? null,
                ];
            }

            $html = view('admin.payments.prints.receipt', compact('payment', 'relatedData'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}