<?php

namespace App\Http\Controllers\Admin\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\PaymentHelper;
use App\Helpers\SmsHelper;

class JobInvoiceController extends Controller
{
    protected $paymentHelper;
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = DB::table('job_books')
                ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
                ->select(
                    'job_books.id',
                    'job_books.job_id',
                    'job_books.job_date',
                    'job_books.engine',
                    'job_books.vehicle_registration_no',
                    'job_books.invoice_amount',
                    'job_books.invoice_status',
                    'job_books.invoice_date',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.address as customer_address'
                )
                ->whereNotNull('job_books.invoice_amount')
                ->orderBy('job_books.id', 'desc');

            return DataTables::of($invoices)
                ->addIndexColumn()
                ->addColumn('job_id', function ($invoice) {
                    return $invoice->job_id;
                })
                ->addColumn('customer_info', function ($invoice) {
                    return '
                    <div class="w-100">
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-user me-1"></i>&nbsp;</span>
                            <span>' . e($invoice->customer_name) . '</span>
                        </div>
                        ' . ($invoice->customer_phone ? '
                        <div class="d-flex gap-2">
                            <span class="fw-bold"><i class="fas fa-phone me-1"></i>&nbsp;</span>
                            <span>' . e($invoice->customer_phone) . '</span>
                        </div>
                        ' : '') . '
                    </div>
                    ';
                })
                ->addColumn('engine', function ($job_book) {

                    return '
                    <div class="w-100">
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-car me-1"></i> &nbsp;</span>
                            <span>' . e($job_book->vehicle_registration_no ?? 'N/A') . '</span>
                        </div>
                        ' . ($job_book->customer_phone ? '
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-cog me-1"></i> &nbsp;</span>
                            <span>' . e($job_book->engine ?? 'N/A') . '</span>
                        </div>
                        ' : '') . '
                    </div>
                    ';
                })
                ->addColumn('invoice_date', function ($invoice) {
                    return $invoice->invoice_date ? date('d-m-Y', strtotime($invoice->invoice_date)) : 'N/A';
                })
                ->addColumn('invoice_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->invoice_amount ?? 0, 2);
                })
                ->addColumn('invoice_status_badge', function ($invoice) {
                    $status = $invoice->invoice_status ?? 'unpaid';
                    $badges = [
                        'paid' => '<span class="badge bg-success">Paid</span>',
                        'partial' => '<span class="badge bg-warning">Partial</span>',
                        'unpaid' => '<span class="badge bg-danger">Unpaid</span>'
                    ];
                    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($invoice) {
                    return view('admin.job_books.partials.invoice-action-btn', ['id' => $invoice->id])->render();
                })
                ->rawColumns(['customer_info', 'invoice_status_badge', 'engine', 'action'])
                ->make(true);
        }

        return view('admin.job_books.invoices-index');
    }

    public function getInvoiceDetails($id)
    {
        try {
            $job = DB::table('job_books')
                ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
                ->where('job_books.id', $id)
                ->select(
                    'job_books.*',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.address as customer_address'
                )
                ->first();

            if (!$job) {
                return response()->json(['success' => false, 'message' => 'Job not found'], 404);
            }

            // Get invoice services
            $services = DB::table('job_invoices')
                ->where('job_book_id', $id)
                ->join('services', 'job_invoices.service_id', '=', 'services.id')
                ->leftJoin('units', 'job_invoices.unit_id', '=', 'units.id')
                ->select(
                    'job_invoices.*',
                    'services.name as service_name',
                    'units.name as unit_name'
                )
                ->get();

            return response()->json([
                'success' => true,
                'job' => $job,
                'services' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function invoiceCreate($job_id = null)
    {
        $services = DB::table('services')->where('status', 1)->get();
        $units = DB::table('units')->where('status', 1)->get();
        return view('admin.job_books.invoice', compact('services', 'units', 'job_id'));
    }

    public function storeInvoice(Request $request)
    {
        try {
            DB::beginTransaction();

            // Delete existing invoice services
            DB::table('job_invoices')->where('job_book_id', $request->job_id)->delete();

            // Insert new services
            foreach ($request->services as $service) {
                DB::table('job_invoices')->insert([
                    'job_book_id' => $request->job_id,
                    'service_id' => $service['service_id'],
                    'unit_id' => $service['unit_id'],
                    'quantity' => $service['quantity'],
                    'price' => $service['price'],
                    'total_price' => $service['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update job_books table with invoice info (including new fields)
            DB::table('job_books')->where('id', $request->job_id)->update([
                'invoice_date' => $request->invoice_date,
                'invoice_amount' => $request->invoice_amount,
                'invoice_discount' => $request->invoice_discount ?? 0,
                'invoice_transport_cost' => $request->invoice_transport_cost ?? 0,  // ✅ Add this
                'invoice_vat' => $request->invoice_vat ?? 0,
                'invoice_vat_type' => $request->invoice_vat_type ?? 'include',  // ✅ Add this
                'invoice_vat_amount' => $request->invoice_vat_amount ?? 0,
                'invoice_description' => $request->invoice_description,
                'invoice_status' => $request->invoice_status,
                'updated_at' => now(),
            ]);

            // ========== পেমেন্ট অংশ ==========
            if ($request->payment_amount && $request->payment_amount > 0) {
                $job = DB::table('job_books')->where('id', $request->job_id)->first();
                $paymentData = $request->payment_data;

                $additionalData = [
                    'narration' => $request->narration,
                    'chq_no' => $paymentData['chq_no'] ?? null,
                    'chq_date' => $paymentData['chq_date'] ?? null,
                    'card_no' => $paymentData['card_no'] ?? null,
                    'online_trx_id' => $paymentData['online_trx_id'] ?? null,
                    'online_trx_dt' => $paymentData['online_trx_dt'] ?? null,
                    'mfs_name' => $paymentData['mfs_name'] ?? null,
                    'bank_code' => $paymentData['bank_code'] ?? null,
                    'bank_ac_no' => $paymentData['bank_ac_no'] ?? null,
                ];

                $entityData = [
                    'table' => 'job_books',
                    'amount_field' => 'invoice_amount',
                    'discount_field' => 'invoice_discount',
                    'paid_field' => 'invoice_paid_amount',
                    'status_field' => 'invoice_status'
                ];

                $result = $this->paymentHelper->processPayment(
                    'customer',
                    $job->customer_id,
                    'job',
                    $request->job_id,
                    $request->payment_amount,
                    $request->payment_mode_id,
                    $additionalData,
                    $entityData
                );

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
            }
            // ======================================

            SmsHelper::sendForEvent('job', 'invoice_create', $request->job_id);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Invoice saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
}


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete invoice services
            DB::table('job_invoices')->where('job_book_id', $id)->delete();

            // Update job_books table
            DB::table('job_books')->where('id', $id)->update([
                'invoice_amount' => 0,
                'invoice_status' => 'unpaid',
                'invoice_description' => null,
                'invoice_date' => null,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Invoice deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete invoice'], 500);
        }
    }
}
