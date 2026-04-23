<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\PaymentHelper;

class JobBookController extends Controller
{
    protected $paymentHelper;
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $job_books = DB::table('job_books')
                ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
                ->select(
                    'job_books.*',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.address as customer_address'
                )
                ->orderBy('job_books.id', 'desc');

            return DataTables::of($job_books)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($job_book) {
                    return $job_book->id;
                })
                ->addColumn('job_id', function ($job_book) {
                    return $job_book->job_id;
                })
                ->addColumn('customer', function ($job_book) {
                    return '
                    <div class="w-100">
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-user me-1"></i> &nbsp;</span>
                            <span>' . e($job_book->customer_name) . '</span>
                        </div>
                        ' . ($job_book->customer_phone ? '
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-phone-alt me-1"></i> &nbsp;</span>
                            <span>' . e($job_book->customer_phone) . '</span>
                        </div>
                        ' : '') . '
                        ' . ($job_book->customer_address ? '
                        <div class="d-flex gap-2">
                            <span class="fw-bold"><i class="fas fa-map-marker-alt me-1"></i> &nbsp;</span>
                            <span>' . e($job_book->customer_address) . '</span>
                        </div>
                        ' : '') . '
                    </div>
                ';
                })
                ->addColumn('date', function ($job_book) {
                    $job_date = $job_book->job_date ? date('d-m-Y', strtotime($job_book->job_date)) : 'N/A';
                    $delivery_date = $job_book->delivery_date ? date('d-m-Y', strtotime($job_book->delivery_date)) : 'N/A';

                    return '
                    <div class="w-100">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold"><i class="fas fa-calendar-alt me-1"></i> Job:</span>
                            <span class="badge bg-info">' . $job_date . '</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold"><i class="fas fa-truck me-1"></i> Delivery:</span>
                            <span class="badge bg-primary">' . $delivery_date . '</span>
                        </div>
                    </div>
                    ';
                })
                ->addColumn('engine', function ($job_book) {
                    return $job_book->engine ? e($job_book->engine) : 'N/A';
                })
                ->addColumn('status_badge', function ($job_book) {
                    $job_status = $job_book->job_status ?? 'pending';
                    $parts_status = $job_book->parts_status ?? 'not_received';

                    // Job Status Badges
                    $jobBadges = [
                        'pending' => 'badge bg-warning',
                        'in_progress' => 'badge bg-info',
                        'completed' => 'badge bg-success',
                        'cancelled' => 'badge bg-danger'
                    ];
                    $jobBadgeClass = $jobBadges[$job_status] ?? 'badge bg-secondary';
                    $jobStatusText = ucfirst(str_replace('_', ' ', $job_status));

                    // Parts Status Badges
                    $partsBadges = [
                        'received' => 'badge bg-success',
                        'not_received' => 'badge bg-danger',
                        'partial' => 'badge bg-warning'
                    ];
                    $partsBadgeClass = $partsBadges[$parts_status] ?? 'badge bg-secondary';
                    $partsStatusText = ucfirst(str_replace('_', ' ', $parts_status));

                    return '
                    <div class="w-100">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold"><i class="fas fa-chart-line me-1"></i> Job:</span>
                            <span class="' . $jobBadgeClass . '">' . $jobStatusText . '</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold"><i class="fas fa-microchip me-1"></i> Parts:</span>
                            <span class="' . $partsBadgeClass . '">' . $partsStatusText . '</span>
                        </div>
                    </div>
                    ';
                })

                ->addColumn('quotation', function ($job_book) {
                    // Check if quotation exists
                    $quotationDate = $job_book->quotation_date;
                    $quotationAmount = $job_book->quotation_amount ?? 0;
                    $quotationStatus = $job_book->quotation_status ?? null;

                    if ($quotationDate) {
                        $amountText = '৳ ' . number_format($quotationAmount, 2);

                        // Status badge for quotation
                        $statusBadges = [
                            'send' => 'badge bg-success',
                            'not_send' => 'badge bg-danger',
                            'pending' => 'badge bg-warning'
                        ];
                        $statusBadgeClass = $statusBadges[$quotationStatus] ?? 'badge bg-secondary';
                        $statusText = $quotationStatus ? ucfirst(str_replace('_', ' ', $quotationStatus)) : 'Not Send';

                        $actionText = '<a href="' . route('admin.job-quotations.create', ['job_id' => $job_book->id]) . '" class="w-100">View Quotation</a>';

                        return '
                            <div class="w-100">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold">Amount:</span>
                                    <strong>' . $amountText . '</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Status:</span>
                                    <span class="' . $statusBadgeClass . '">' . $statusText . '</span>
                                </div>
                                ' . $actionText . '
                            </div>
                        ';
                    } else {
                        $actionText = '<a href="' . route('admin.job-quotations.create', ['job_id' => $job_book->id]) . '" class="w-100">Generate Quotation</a>';
                        return '
                            <div class="w-100 text-center">
                                ' . $actionText . '
                            </div>
                        ';
                    }
                })
                ->addColumn('invoice', function ($job_book) {
                    // Check if invoice exists
                    $invoiceDate = $job_book->invoice_date ?? null;
                    $invoiceAmount = $job_book->invoice_amount ?? 0;
                    $invoiceStatus = $job_book->invoice_status ?? null;

                    if ($invoiceDate) {
                        $amountText = '৳ ' . number_format($invoiceAmount, 2);

                        // Status badge for invoice
                        $statusBadges = [
                            'paid' => 'badge bg-success',
                            'unpaid' => 'badge bg-danger',
                            'partial' => 'badge bg-warning'
                        ];
                        $statusBadgeClass = $statusBadges[$invoiceStatus] ?? 'badge bg-secondary';
                        $statusText = $invoiceStatus ? ucfirst($invoiceStatus) : 'Generated';

                        $actionText = '<a href="' . route('admin.job-invoices.create', ['job_id' => $job_book->id]) . '" class=" w-100">View Invoice</a>';

                        return '
                            <div class="w-100">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold">Amount:</span>
                                    <strong>' . $amountText . '</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold">Status:</span>
                                    <span class="' . $statusBadgeClass . '">' . $statusText . '</span>
                                </div>
                                ' . $actionText . '
                            </div>
                        ';
                    } else {
                        $actionText = '<a href="' . route('admin.job-invoices.create', ['job_id' => $job_book->id]) . '" class="w-100">generate Invoice</a>';
                        return '
                            <div class="w-100 text-center">
                                ' . $actionText . '
                            </div>
                        ';
                    }
                })
                ->addColumn('action', function ($job_book) {
                    return view('admin.job_books.partials.action-btn-view', ['id' => $job_book->id, 'customer_id' => $job_book->customer_id])->render();
                })
                ->rawColumns(['customer', 'date', 'status_badge', 'quotation', 'invoice', 'action'])
                ->make(true);
        }

        return view('admin.job_books.index');
    }

    public function create()
    {
        $employees = DB::table('employees')
            ->where('status', 1)
            ->select('id', 'name')
            ->get();
        $parts = DB::table('parts')->orderBy('id', 'desc')->where('status', 1)->get();
        $sizes = DB::table('sizes')->where('status', 1)->get();

        return view('admin.job_books.create', compact('employees', 'parts', 'sizes'));
    }

    public function store(Request $request)
    {
        // dd($request->input());
        // Validate the request
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'job_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'engine' => 'nullable|string|max:100',
            'job_status' => 'required|in:pending,in_progress,completed,cancelled',
            'descriptions' => 'nullable|string',
            'documents' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'assign_to' => 'nullable|array',
            'assign_to.*' => 'exists:employees,id',
            'job_parts' => 'required|array|min:1',
            'job_parts.*.part_id' => 'required|exists:parts,id',
            'job_parts.*.size_id' => 'required|exists:sizes,id',
            'job_parts.*.quantity' => 'required|integer|min:1',
            'job_parts.*.single_price' => 'required|numeric|min:0',
            'job_parts.*.total_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle documents upload

            $photoPath = null;
            if ($request->hasFile('documents') && $request->file('documents')->isValid()) {
                try {
                    $photoFile = $request->file('documents');

                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();

                    // Store in public/uploads/employees folder
                    $photoPath = 'public/uploads/job-books/' . $filename;
                    $photoFile->move(public_path('uploads/job-books'), $filename);
                } catch (\Exception $e) {
                    $photoPath = null;
                }
            }


            // Generate Job ID (e.g., JOB-00001)
            $lastJob = DB::table('job_books')->orderBy('id', 'desc')->first();
            $lastId = $lastJob ? $lastJob->id : 0;
            $jobId = 'JOB-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            // Convert assign_to array to comma-separated string
            $assignToString = null;
            if ($request->has('assign_to') && is_array($request->assign_to)) {
                $assignToString = implode(',', $request->assign_to);
            }

            // Insert into job_books
            $jobBookId = DB::table('job_books')->insertGetId([
                'job_id' => $jobId,
                'branch_id' => 1,
                'customer_id' => $request->customer_id,
                'job_date' => $request->job_date,
                'delivery_date' => $request->delivery_date,
                'engine' => $request->engine,
                'descriptions' => $request->descriptions,
                'job_status' => $request->job_status,
                'parts_status' => 'not_received',
                'documents' => $photoPath,
                'assign_to' => $assignToString, // Store as comma-separated string
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->user()->id
            ]);

            // Insert parts into job_parts
            $totalPartsPrice = 0;
            foreach ($request->job_parts as $part) {
                DB::table('job_parts')->insert([
                    'job_book_id' => $jobBookId,
                    'parts_id' => $part['part_id'],
                    'size_id' => $part['size_id'],
                    'quantity' => $part['quantity'],
                    'single_price' => $part['single_price'],
                    'total_price' => $part['total_price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $totalPartsPrice += $part['total_price'];
            }

            // Update total price in job_books
            DB::table('job_books')->where('id', $jobBookId)->update([
                'parts_amount' => $totalPartsPrice,
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.job-books.index')
                ->with('success', 'Job created successfully! Job ID: ' . $jobId);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create job: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $jobBook = DB::table('job_books')
            ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
            ->select(
                'job_books.*',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'customers.address as customer_address'
            )
            ->where('job_books.id', $id)
            ->first();

        if (!$jobBook) {
            return redirect()->route('admin.job-books.index')
                ->with('error', 'Job not found');
        }

        // Get assigned employees names
        if ($jobBook->assign_to) {
            $assignToIds = explode(',', $jobBook->assign_to);
            $employees = DB::table('employees')
                ->whereIn('id', $assignToIds)
                ->pluck('name')
                ->toArray();
            $jobBook->assign_to_names = implode(', ', $employees);
        }

        // Get job parts
        $jobParts = DB::table('job_parts')
            ->leftJoin('parts', 'job_parts.parts_id', '=', 'parts.id')
            ->leftJoin('sizes', 'job_parts.size_id', '=', 'sizes.id')
            ->where('job_parts.job_book_id', $id)
            ->select('job_parts.*', 'parts.name as part_name',  'sizes.name as size_name')
            ->get();

        // Get quotations
        $quotations = DB::table('job_quotations')
            ->leftJoin('services', 'job_quotations.service_id', '=', 'services.id')
            ->where('job_quotations.job_book_id', $id)
            ->select('job_quotations.*', 'services.name as service_name')
            ->get();

        // Get invoices
        $invoices = DB::table('job_invoices')
            ->leftJoin('services', 'job_invoices.service_id', '=', 'services.id')
            ->where('job_invoices.job_book_id', $id)
            ->select('job_invoices.*', 'services.name as service_name')
            ->get();

        return view('admin.job_books.show', compact('jobBook', 'jobParts', 'quotations', 'invoices'));
    }

    public function edit($id)
    {
        $jobBook = DB::table('job_books')->where('id', $id)->first();
        if (!$jobBook) {
            return redirect()->route('admin.job-books.index')->with('error', 'Job not found');
        }

        $employees = DB::table('employees')->where('status', 1)->get();
        $parts = DB::table('parts')->orderBy('id', 'desc')->where('status', 1)->get();
        $sizes = DB::table('sizes')->where('status', 1)->get();

        // Get assigned employees IDs
        $assignedEmployees = [];
        if ($jobBook->assign_to) {
            $assignedEmployees = explode(',', $jobBook->assign_to);
        }

        // Get customer data
        $jobBook->customer = DB::table('customers')->where('id', $jobBook->customer_id)->first();

        return view('admin.job_books.edit', compact('jobBook', 'employees', 'parts', 'sizes', 'assignedEmployees'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'job_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'engine' => 'nullable|string|max:100',
            'job_status' => 'required|in:pending,in_progress,completed,cancelled',
            'descriptions' => 'nullable|string',
            'documents' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'assign_to' => 'nullable|array',
            'assign_to.*' => 'exists:employees,id',
            'job_parts' => 'required|array|min:1',
            'job_parts.*.part_id' => 'required|exists:parts,id',
            'job_parts.*.size_id' => 'required|exists:sizes,id',
            'job_parts.*.quantity' => 'required|integer|min:1',
            'job_parts.*.single_price' => 'required|numeric|min:0',
            'job_parts.*.total_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $job_book = DB::table('job_books')->where('id', $id)->first();

        if (!$job_book) {
            return redirect()->route('admin.job-books.index')
                ->with('error', 'Job not found');
        }
        try {
            DB::beginTransaction();

            // Handle photo upload
            $photoPath = $job_book->documents; // Keep existing photo by default

            if ($request->hasFile('documents') && $request->file('documents')->isValid()) {
                try {
                    // Delete old photo if exists
                    if ($job_book->documents && file_exists(public_path($job_book->documents))) {
                        unlink(public_path($job_book->documents));
                    }

                    $photoFile = $request->file('documents');
                    $filename = time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();
                    $photoPath = 'public/uploads/job-books/' . $filename;
                    $photoFile->move(public_path('uploads/job-books'), $filename);
                } catch (\Exception $e) {
                    $photoPath = $job_book->documents; // Keep old photo on error
                }
            }

            // Convert assign_to array to comma-separated string
            $assignToString = null;
            if ($request->has('assign_to') && is_array($request->assign_to)) {
                $assignToString = implode(',', $request->assign_to);
            }

            // Update job_books
            DB::table('job_books')->where('id', $id)->update([
                'customer_id' => $request->customer_id,
                'job_date' => $request->job_date,
                'delivery_date' => $request->delivery_date,
                'engine' => $request->engine,
                'descriptions' => $request->descriptions,
                'job_status' => $request->job_status,
                'documents' => $photoPath,
                'assign_to' => $assignToString,
                'updated_at' => now(),
            ]);

            // Update parts - delete old parts and insert new ones
            // Or update existing ones based on ID

            $existingPartIds = [];
            $totalPartsPrice = 0;

            foreach ($request->job_parts as $part) {
                if (isset($part['id']) && !empty($part['id'])) {
                    // Update existing part
                    DB::table('job_parts')->where('id', $part['id'])->update([
                        'parts_id' => $part['part_id'],
                        'size_id' => $part['size_id'],
                        'quantity' => $part['quantity'],
                        'single_price' => $part['single_price'],
                        'total_price' => $part['total_price'],
                        'updated_at' => now(),
                    ]);
                    $existingPartIds[] = $part['id'];
                } else {
                    // Insert new part
                    $newId = DB::table('job_parts')->insertGetId([
                        'job_book_id' => $id,
                        'parts_id' => $part['part_id'],
                        'size_id' => $part['size_id'],
                        'quantity' => $part['quantity'],
                        'single_price' => $part['single_price'],
                        'total_price' => $part['total_price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $existingPartIds[] = $newId;
                }
                $totalPartsPrice += $part['total_price'];
            }

            // Delete parts that were removed
            DB::table('job_parts')
                ->where('job_book_id', $id)
                ->whereNotIn('id', $existingPartIds)
                ->delete();

            // Update total price
            DB::table('job_books')->where('id', $id)->update([
                'parts_amount' => $totalPartsPrice,
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.job-books.edit', $id)
                ->with('success', 'Job updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update job: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // First delete related records (job_parts, job_quotations, job_invoices, payments)
            DB::table('job_parts')->where('job_book_id', $id)->delete();
            DB::table('job_quotations')->where('job_book_id', $id)->delete();
            DB::table('job_invoices')->where('job_book_id', $id)->delete();
            DB::table('payments')->where('type_id', $id)->where('type', 'job')->delete();

            // Then delete job_book
            DB::table('job_books')->where('id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Failed to delete job: ' . $e->getMessage()], 500);
        }
    }
    public function searchJobs(Request $request)
    {
        $search = $request->get('search');
        $jobs = DB::table('job_books')
            ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
            ->where('job_books.job_id', 'LIKE', "%{$search}%")
            ->orWhere('customers.name', 'LIKE', "%{$search}%")
            ->select('job_books.id', 'job_books.job_id', 'customers.name as customer_name', 'job_books.job_date')
            ->paginate(10);

        $results = [];
        foreach ($jobs as $job) {
            $results[] = [
                'id' => $job->id,
                'job_id' => $job->job_id,
                'customer_name' => $job->customer_name,
                'job_date' => $job->job_date
            ];
        }

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $jobs->hasMorePages()]
        ]);
    }

    public function getJobDetails($id)
    {
        $job = DB::table('job_books')
            ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
            ->where('job_books.id', $id)
            ->select('job_books.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'customers.address as customer_address')
            ->first();

        $services = DB::table('job_quotations')
            ->where('job_book_id', $id)
            ->join('services', 'job_quotations.service_id', '=', 'services.id')
            ->leftJoin('units', 'job_quotations.unit_id', '=', 'units.id')  // Add this join
            ->select(
                'job_quotations.*',
                'services.name as service_name',
                'units.name as unit_name',      // Add unit name
                'units.id as unit_id'           // Add unit id
            )
            ->get();

        return response()->json([
            'success' => true,
            'job' => $job,
            'services' => $services
        ]);
    }
    public function quotationCreate()
    {
        $services = DB::table('services')->where('status', 1)->get();
        $units = DB::table('units')->where('status', 1)->get();
        return view('admin.job_books.quotations', compact('services', 'units'));
    }
    public function storeQuotation(Request $request)
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->services as $service) {
                $totalAmount += $service['total'];
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
                'quotation_description' => $request->quotation_description,
                'quotation_amount' => $totalAmount,
                'quotation_status' => $request->quotation_status ?? 'not_send', // Add this line
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Quotation saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function invoiceCreate($job_id = null)
    {
        $services = DB::table('services')->where('status', 1)->get();
        $units = DB::table('units')->where('status', 1)->get();
        return view('admin.job_books.invoice', compact('services', 'units', 'job_id'));
    }

    public function getInvoiceDetails($id)
    {
        $job = DB::table('job_books')
            ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
            ->where('job_books.id', $id)
            ->select('job_books.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'customers.address as customer_address')
            ->first();

        $services = DB::table('job_invoices')
            ->where('job_book_id', $id)
            ->join('services', 'job_invoices.service_id', '=', 'services.id')
            ->leftJoin('units', 'job_invoices.unit_id', '=', 'units.id')  // Add this join
            ->select(
                'job_invoices.*',
                'services.name as service_name',
                'units.name as unit_name',
                'units.id as unit_id'
            )
            ->get();

        return response()->json([
            'success' => true,
            'job' => $job,
            'services' => $services
        ]);
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

            // Update job_books table with invoice info
            DB::table('job_books')->where('id', $request->job_id)->update([
                'invoice_date' => $request->invoice_date,
                'invoice_amount' => $request->invoice_amount,
                'invoice_discount' => $request->invoice_discount,
                'invoice_description' => $request->invoice_description,
                'invoice_status' => $request->invoice_status,
                'updated_at' => now(),
            ]);

            // ========== পেমেন্ট অংশ যোগ করো ==========
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

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Invoice saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function print(Request $request)
    {
        try {
            $jobId = $request->job_id;
            $documents = $request->documents;

            // Fetch job data using DB queries
            $job = DB::table('job_books')
                ->where('id', $jobId)
                ->first();

            if (!$job) {
                throw new \Exception('Job not found');
            }

            // Fetch customer data
            $customer = DB::table('customers')
                ->where('id', $job->customer_id)
                ->first();

            // Fetch job parts
            $jobParts = DB::table('job_parts')
                ->leftJoin('parts', 'job_parts.parts_id', '=', 'parts.id')
                ->leftJoin('sizes', 'job_parts.size_id', '=', 'sizes.id')
                ->where('job_parts.job_book_id', $jobId)
                ->select(
                    'job_parts.*',
                    'parts.name as part_name',
                    'sizes.name as size_name',
                    'parts.description as part_description'
                )
                ->get();

            // Fetch quotation items
            $quotationItems = DB::table('job_quotations')
                ->leftJoin('services', 'job_quotations.service_id', '=', 'services.id')
                ->leftJoin('units', 'job_quotations.unit_id', '=', 'units.id')
                ->where('job_quotations.job_book_id', $jobId)
                ->select(
                    'job_quotations.*',
                    'services.name as service_name',
                    'services.description as service_description',
                    'units.name as unit_name'
                )
                ->get();

            // Fetch invoice items
            $invoiceItems = DB::table('job_invoices')
                ->leftJoin('services', 'job_invoices.service_id', '=', 'services.id')
                ->leftJoin('units', 'job_invoices.unit_id', '=', 'units.id')
                ->where('job_invoices.job_book_id', $jobId)
                ->select(
                    'job_invoices.*',
                    'services.name as service_name',
                    'services.description as service_description',
                    'units.name as unit_name'
                )
                ->get();

            // Prepare job data
            $jobData = (object) [
                'id' => $job->id,
                'job_id' => $job->job_id,
                'date' => $job->job_date,
                'engine' => $job->engine,
                'status' => $job->job_status,
                'description' => $job->descriptions,
                'parts_status' => $job->parts_status,
                'delivery_date' => $job->delivery_date,
                'parts_amount' => $job->parts_amount,
                'invoice_amount' => $job->invoice_amount,
                'invoice_discount' => $job->invoice_discount,
                'quotation_amount' => $job->quotation_amount,
                'quotation_date' => $job->quotation_date,
                'invoice_date' => $job->invoice_date,
                'customer' => $customer,
                'jobParts' => $jobParts,
                'quotation' => (object)[
                    'quotation_date' => $job->quotation_date,
                    'quotation_amount' => $job->quotation_amount,
                    'quotation_description' => $job->quotation_description,
                    'quotation_status' => $job->quotation_status,
                    'items' => $quotationItems
                ],
                'invoice' => (object)[
                    'invoice_date' => $job->invoice_date,
                    'invoice_amount' => $job->invoice_amount,
                    'invoice_discount' => $job->invoice_discount,
                    'invoice_description' => $job->invoice_description,
                    'invoice_status' => $job->invoice_status,
                    'invoice_paid_amount' => $job->invoice_paid_amount ?? 0,
                    'items' => $invoiceItems
                ]
            ];

            // Generate HTML for print
            $html = view('admin.job_books.prints.print-layout', compact('jobData', 'documents'))->render();

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

    public function getPaymentDetails($id)
    {
        $entityData = [
            'table' => 'job_books',
            'amount_field' => 'invoice_amount',
            'discount_field' => 'invoice_discount',
            'paid_field' => 'invoice_paid_amount',
            'status_field' => 'invoice_status'
        ];

        $result = $this->paymentHelper->getPaymentDetails('job', $id, $entityData);
        return response()->json($result);
    }

    public function getPaymentHistory($typeId)
    {
        $result = $this->paymentHelper->getPaymentHistory('job', $typeId);
        return response()->json($result);
    }

    public function getPayment($paymentId)
    {
        $result = $this->paymentHelper->getSinglePayment($paymentId);
        return response()->json($result);
    }

    public function processPayment(Request $request)
    {
        try {
            $paymentMode = DB::table('acc_payment_modes')->where('id', $request->payment_mode_id)->first();
            if (!$paymentMode) {
                return response()->json(['success' => false, 'message' => 'Payment mode not found'], 422);
            }

            $validationRules = $this->getValidationRules($paymentMode->mode_name);
            $validationRules['payment_amount'] = 'required|numeric|min:0.01';
            $validationRules['payment_mode_id'] = 'required|exists:acc_payment_modes,id';
            $validationRules['type_id'] = 'required|exists:job_books,id';

            $request->validate($validationRules);

            $job = DB::table('job_books')->where('id', $request->type_id)->first();
            if (!$job) {
                return response()->json(['success' => false, 'message' => 'Job not found'], 404);
            }

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
                $request->type_id,
                $request->payment_amount,
                $request->payment_mode_id,
                $additionalData,
                $entityData
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePayment(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:payments,id',
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_mode_id' => 'required|exists:acc_payment_modes,id',
            ]);

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

            $entityData = [
                'table' => 'job_books',
                'amount_field' => 'invoice_amount',
                'discount_field' => 'invoice_discount',
                'paid_field' => 'invoice_paid_amount',
                'status_field' => 'invoice_status'
            ];

            $result = $this->paymentHelper->updatePayment(
                $request->payment_id,
                $request->payment_amount,
                $request->payment_mode_id,
                $additionalData,
                $entityData
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePayment(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:payments,id',
                'type_id' => 'required|exists:job_books,id',
            ]);

            $entityData = [
                'table' => 'job_books',
                'amount_field' => 'invoice_amount',
                'discount_field' => 'invoice_discount',
                'paid_field' => 'invoice_paid_amount',
                'status_field' => 'invoice_status'
            ];

            $result = $this->paymentHelper->deletePayment($request->payment_id, $request->type_id, $entityData);
            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getValidationRules($modeName)
    {
        switch ($modeName) {
            case 'Cheque':
                return ['chq_no' => 'required|string', 'chq_date' => 'required|date'];
            case 'Card':
                return ['card_no' => 'required|string'];
            case 'Mobile Banking':
                return ['mfs_name' => 'required|string|in:Bkash,Nagad,Rocket,Ok Wallet,Upay'];
            case 'Internet Banking':
                return ['bank_code' => 'required|string', 'bank_ac_no' => 'required|string'];
            default:
                return [];
        }
    }
}
