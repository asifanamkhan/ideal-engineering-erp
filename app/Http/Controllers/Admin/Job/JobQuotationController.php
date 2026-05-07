<?php

namespace App\Http\Controllers\Admin\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\PaymentHelper;
use App\Helpers\SmsHelper;

class JobQuotationController extends Controller
{
    protected $paymentHelper;
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $quotations = DB::table('job_books')
                ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
                ->select(
                    'job_books.id',
                    'job_books.job_id',
                    'job_books.job_date',
                    'job_books.engine',
                    'job_books.vehicle_registration_no',
                    'job_books.quotation_amount',
                    'job_books.quotation_status',
                    'job_books.quotation_date',
                    'job_books.quotation_description',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.address as customer_address'
                )
                ->whereNotNull('job_books.quotation_amount')
                ->orderBy('job_books.id', 'desc');

            return DataTables::of($quotations)
                ->addIndexColumn()
                ->addColumn('job_id', function ($quotation) {
                    return $quotation->job_id;
                })
                ->addColumn('customer_info', function ($quotation) {
                    return '
                    <div class="w-100">
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-user me-1"></i>&nbsp;</span>
                            <span>' . e($quotation->customer_name) . '</span>
                        </div>
                        ' . ($quotation->customer_phone ? '
                        <div class="d-flex gap-2">
                            <span class="fw-bold"><i class="fas fa-phone me-1"></i>&nbsp;</span>
                            <span>' . e($quotation->customer_phone) . '</span>
                        </div>
                        ' : '') . '
                    </div>
                    ';
                })
                ->addColumn('engine', function ($job_book) {

                    return '
                    <div class="w-100">
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-car me-1"></i> &nbsp; </span>
                            <span>' . e($job_book->vehicle_registration_no ?? 'N/A') . '</span>
                        </div>
                        ' . ($job_book->customer_phone ? '
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-cog me-1"></i> &nbsp; </span>
                            <span>' . e($job_book->engine ?? 'N/A') . '</span>
                        </div>
                        ' : '') . '
                    </div>
                    ';
                })
                ->addColumn('quotation_date', function ($quotation) {
                    return $quotation->quotation_date ? date('d-m-Y', strtotime($quotation->quotation_date)) : 'N/A';
                })
                ->addColumn('quotation_amount', function ($quotation) {
                    return '৳ ' . number_format($quotation->quotation_amount ?? 0, 2);
                })
                ->addColumn('quotation_status_badge', function ($quotation) {
                    $status = $quotation->quotation_status ?? 'not_send';
                    $badges = [
                        'send' => '<span class="badge bg-success">Send</span>',
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'not_send' => '<span class="badge bg-danger">Not Send</span>'
                    ];
                    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                })
                ->addColumn('action', function ($quotation) {
                    return view('admin.job_books.partials.quotation-action-btn', ['id' => $quotation->id])->render();
                })
                ->rawColumns(['customer_info', 'quotation_status_badge', 'engine', 'action'])
                ->make(true);
        }

        return view('admin.job_books.quotations-index');
    }

    public function quotationCreate()
    {
        $services = DB::table('services')->where('status', 1)->get();
        $units = DB::table('units')->where('status', 1)->get();
        return view('admin.job_books.quotations', compact('services', 'units'));
    }
    public function getDetails($id)
    {
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

        // Get quotation items
        $services = DB::table('job_quotations')
            ->where('job_book_id', $id)
            ->join('services', 'job_quotations.service_id', '=', 'services.id')
            ->leftJoin('units', 'job_quotations.unit_id', '=', 'units.id')
            ->select(
                'job_quotations.*',
                'services.name as service_name',
                'units.name as unit_name',
                'units.id as unit_id'
            )
            ->get();

        // Get quotation ID if exists
        $quotationId = DB::table('job_quotations')->where('job_book_id', $id)->value('id');

        return response()->json([
            'success' => true,
            'job' => $job,
            'services' => $services,
            'quotation_id' => $quotationId
        ]);
    }
    public function storeQuotation(Request $request)
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->services as $service) {
                $totalAmount += $service['total'];
            }

            // Get VAT data
            $vatPercent = $request->quotation_vat ?? 0;
            $vatType = $request->quotation_vat_type ?? 'include';

            // Calculate VAT amount based on type
            $vatAmount = 0;
            $grandTotal = $totalAmount;

            if ($vatType == 'exclude' && $vatPercent > 0) {
                $vatAmount = ($totalAmount * $vatPercent) / 100;
                $grandTotal = $totalAmount + $vatAmount;
            } elseif ($vatType == 'include' && $vatPercent > 0) {
                // VAT included - calculate reverse
                $vatAmount = ($totalAmount * $vatPercent) / (100 + $vatPercent);
                $grandTotal = $totalAmount;
            }

            // First, delete existing quotations for this job
            DB::table('job_quotations')->where('job_book_id', $request->job_id)->delete();

            // Insert new services
            foreach ($request->services as $service) {
                DB::table('job_quotations')->insert([
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

            // Update job_books table with quotation info
            DB::table('job_books')->where('id', $request->job_id)->update([
                'quotation_date' => $request->quotation_date,
                'quotation_subject' => $request->quotation_subject,
                'quotation_description' => $request->quotation_description,
                'quotation_amount' => $grandTotal,
                'quotation_vat' => $vatPercent,
                'quotation_vat_type' => $vatType,  // ✅ Add this
                'quotation_vat_amount' => $vatAmount,
                'quotation_status' => $request->quotation_status ?? 'not_send',
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Quotation saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function convertToInvoice(Request $request)
    {
        try {
            DB::beginTransaction();

            $jobId = $request->job_id;

            // Check if job exists
            $job = DB::table('job_books')->where('id', $jobId)->first();
            if (!$job) {
                throw new \Exception('Job not found');
            }

            // Check if invoice already exists
            $existingInvoice = DB::table('job_invoices')->where('job_book_id', $jobId)->first();
            if ($existingInvoice) {
                throw new \Exception('Invoice already exists for this job');
            }

            // Get services from request or database
            $services = $request->services;

            if (!$services || empty($services)) {
                $quotationServices = DB::table('job_quotations')
                    ->where('job_book_id', $jobId)
                    ->get();

                if ($quotationServices->isEmpty()) {
                    throw new \Exception('No quotation found for this job');
                }

                $services = [];
                foreach ($quotationServices as $service) {
                    $services[] = [
                        'service_id' => $service->service_id,
                        'unit_id' => $service->unit_id,
                        'quantity' => $service->quantity,
                        'price' => $service->price,
                        'total' => $service->total_price
                    ];
                }
            }

            // Calculate total amount
            $totalAmount = 0;
            foreach ($services as $service) {
                $totalAmount += $service['total'];
            }

            // Get quotation VAT data
            $quotationVat = $job->quotation_vat ?? 0;
            $quotationVatType = $job->quotation_vat_type ?? 'include';
            $quotationVatAmount = $job->quotation_vat_amount ?? 0;

            // Calculate invoice amount with VAT
            $invoiceAmount = $totalAmount;
            $invoiceVatAmount = 0;

            if ($quotationVatType == 'exclude' && $quotationVat > 0) {
                $invoiceVatAmount = ($totalAmount * $quotationVat) / 100;
                $invoiceAmount = $totalAmount + $invoiceVatAmount;
            } elseif ($quotationVatType == 'include' && $quotationVat > 0) {
                $invoiceVatAmount = $quotationVatAmount;
                $invoiceAmount = $totalAmount;
            }

            // Delete existing invoice services
            DB::table('job_invoices')->where('job_book_id', $jobId)->delete();

            // Insert new invoice services
            foreach ($services as $service) {
                DB::table('job_invoices')->insert([
                    'job_book_id' => $jobId,
                    'service_id' => $service['service_id'],
                    'unit_id' => $service['unit_id'],
                    'quantity' => $service['quantity'],
                    'price' => $service['price'],
                    'total_price' => $service['total'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Update job_books with invoice info (including VAT)
            DB::table('job_books')->where('id', $jobId)->update([
                'invoice_date' => now(),
                'invoice_amount' => $invoiceAmount,
                'invoice_discount' => 0,
                'invoice_vat' => $quotationVat,
                'invoice_vat_type' => $quotationVatType,
                'invoice_vat_amount' => $invoiceVatAmount,
                'invoice_description' => 'Converted from quotation',
                'invoice_status' => 'unpaid',
                'updated_at' => now()
            ]);

            DB::commit();
            SmsHelper::sendForEvent('job', 'invoice_create', $jobId);

            return response()->json([
                'success' => true,
                'message' => 'Quotation converted to invoice successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete quotation services
            DB::table('job_quotations')->where('job_book_id', $id)->delete();

            // Update job_books table
            DB::table('job_books')->where('id', $id)->update([
                'quotation_amount' => 0,
                'quotation_status' => 'not_send',
                'quotation_description' => null,
                'quotation_date' => null,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Quotation deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete quotation'], 500);
        }
    }
}
