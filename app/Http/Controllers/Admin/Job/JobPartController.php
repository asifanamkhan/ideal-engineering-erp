<?php

namespace App\Http\Controllers\Admin\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class JobPartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $jobs = DB::table('job_books')
                ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
                ->select(
                    'job_books.id',
                    'job_books.job_id',
                    'job_books.job_date',
                    'job_books.engine',
                    'job_books.vehicle_registration_no',
                    'job_books.parts_amount',
                    'job_books.parts_status',
                    'job_books.parts_description',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.address as customer_address'
                )
                ->whereNotNull('job_books.parts_amount')
                ->orderBy('job_books.id', 'desc');

            return DataTables::of($jobs)
                ->addIndexColumn()
                ->addColumn('job_id', function ($job) {
                    return $job->job_id;
                })
                ->addColumn('customer_info', function ($job) {
                    return '
                    <div class="w-100">
                        <div class="d-flex gap-2 mb-1">
                            <span class="fw-bold"><i class="fas fa-user me-1">&nbsp;</i></span>
                            <span>' . e($job->customer_name) . '</span>
                        </div>
                        ' . ($job->customer_phone ? '
                        <div class="d-flex gap-2">
                            <span class="fw-bold"><i class="fas fa-phone me-1">&nbsp;</i></span>
                            <span>' . e($job->customer_phone) . '</span>
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
                ->addColumn('job_date', function ($job) {
                    return date('d-m-Y', strtotime($job->job_date));
                })
                ->addColumn('parts_amount', function ($job) {
                    return '৳ ' . number_format($job->parts_amount ?? 0, 2);
                })
                ->addColumn('parts_status_badge', function ($job) {
                    $status = $job->parts_status ?? 'not_received';
                    $badges = [
                        'received' => '<span class="badge bg-success">Received</span>',
                        'partial' => '<span class="badge bg-warning">Partial</span>',
                        'not_received' => '<span class="badge bg-danger">Not Received</span>'
                    ];
                    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                })
                ->addColumn('action', function ($job) {
                    return view('admin.job_books.partials.parts-action-btn', ['id' => $job->id])->render();
                })
                ->rawColumns(['customer_info', 'parts_status_badge', 'engine','action'])
                ->make(true);
        }

        return view('admin.job_books.parts-index');
    }
    public function create($job_id = null)
    {
        $parts = DB::table('parts')->where('status', 1)->get();
        $sizes = DB::table('sizes')->where('status', 1)->get();
        return view('admin.job_books.parts', compact('parts', 'sizes', 'job_id'));
    }

    public function getPartDetails($id)
    {
        $job = DB::table('job_books')
            ->leftJoin('customers', 'job_books.customer_id', '=', 'customers.id')
            ->where('job_books.id', $id)
            ->select('job_books.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'customers.address as customer_address')
            ->first();

        $parts = DB::table('job_parts')
            ->where('job_book_id', $id)
            ->join('parts', 'job_parts.parts_id', '=', 'parts.id')
            ->leftJoin('sizes', 'job_parts.size_id', '=', 'sizes.id')
            ->select(
                'job_parts.*',
                'parts.name as part_name',
                'parts.brand',
                'parts.model',
                'sizes.name as size_name',
                'sizes.id as size_id'
            )
            ->get();

        return response()->json([
            'success' => true,
            'job' => $job,
            'parts' => $parts
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Delete existing parts
            DB::table('job_parts')->where('job_book_id', $request->job_id)->delete();

            // Insert new parts
            $totalAmount = 0;
            foreach ($request->parts as $part) {
                DB::table('job_parts')->insert([
                    'job_book_id' => $request->job_id,
                    'parts_id' => $part['part_id'],
                    'size_id' => $part['size_id'],
                    'quantity' => $part['quantity'],
                    'single_price' => $part['price'],
                    'total_price' => $part['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $totalAmount += $part['total'];
            }

            // Update job_books table with parts info
            DB::table('job_books')->where('id', $request->job_id)->update([
                'parts_amount' => $totalAmount,
                'parts_status' => $request->parts_status,
                'parts_description' => $request->parts_description,
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Parts saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete job parts
            DB::table('job_parts')->where('job_book_id', $id)->delete();

            // Update job_books table
            DB::table('job_books')->where('id', $id)->update([
                'parts_amount' => 0,
                'parts_status' => 'not_received',
                'parts_description' => null,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Parts deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete parts'], 500);
        }
    }
    public function edit($id)
    {
        $parts = DB::table('parts')->where('status', 1)->get();
        $sizes = DB::table('sizes')->where('status', 1)->get();
        return view('admin.job_books.parts', compact('parts', 'sizes', 'id'));
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Delete existing parts
            DB::table('job_parts')->where('job_book_id', $id)->delete();

            // Insert new parts
            $totalAmount = 0;
            foreach ($request->parts as $part) {
                DB::table('job_parts')->insert([
                    'job_book_id' => $id,
                    'parts_id' => $part['part_id'],
                    'size_id' => $part['size_id'],
                    'quantity' => $part['quantity'],
                    'single_price' => $part['price'],
                    'total_price' => $part['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $totalAmount += $part['total'];
            }

            // Update job_books table
            DB::table('job_books')->where('id', $id)->update([
                'parts_amount' => $totalAmount,
                'parts_status' => $request->parts_status,
                'parts_description' => $request->parts_description,
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Parts updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
