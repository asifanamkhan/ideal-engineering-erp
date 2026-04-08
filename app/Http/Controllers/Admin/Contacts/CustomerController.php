<?php

namespace App\Http\Controllers\Admin\Contacts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $customers = DB::table('customers')
                ->leftJoin('branches', 'customers.branch_id', '=', 'branches.id')
                ->select(['customers.*', 'branches.name as branch'])
                ->orderBy('customers.id', 'desc');

            // Apply search filter if search term exists
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];
                $customers->where(function ($query) use ($searchTerm) {
                    $query->where('customers.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customers.email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customers.phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customers.customer_id', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customers.address', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('branches.name', 'LIKE', "%{$searchTerm}%");
                });
            }

            return DataTables::of($customers)
                ->addIndexColumn()
                ->addColumn('customer_id', function ($customer) {
                    return '<span class="badge bg-info">' . ($customer->customer_id ?? 'N/A') . '</span>';
                })
                ->addColumn('name', function ($customer) {
                    return '<strong>' . e($customer->name) . '</strong>';
                })
                ->addColumn('phone', function ($customer) {
                    return $customer->phone ?? 'N/A';
                })
                ->addColumn('type', function ($customer) {
                    $type = $customer->type ?? 'individual';
                    $badgeClass = $type == 'business' ? 'bg-primary' : 'bg-success';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($type) . '</span>';
                })
                ->addColumn('status_badge', function ($customer) {
                    $status = $customer->status ?? 1;
                    if ($status == 1 || $status == 'active' || $status == 'Active') {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('action', function ($customer) {
                    return view('admin.contacts.customers.partials.action-btn-view', ['id' => $customer->id])->render();
                })
                ->rawColumns(['customer_id', 'name', 'type', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.contacts.customers.index');
    }
    public function getForm()
    {
        return view('admin.contacts.customers.partials.modal-form');
    }
    private function generateCustomerId()
    {
        // Get the last customer ID
        $lastCustomer = DB::table('customers')
            ->where('customer_id', 'LIKE', 'CUST%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastNumber = intval(substr($lastCustomer->customer_id, 4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $formattedNumber = str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        return 'CUS' . $formattedNumber;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'type' => 'required|in:individual,business',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $customerId = $this->generateCustomerId();

            $insertedId = DB::table('customers')->insertGetId([
                'customer_id' => $customerId,
                'branch_id' => 1,
                'name' => $request->name,
                'email' => $request->email,  // Can be null
                'phone' => $request->phone,
                'type' => $request->type,
                'address' => $request->address,
                'reference' => $request->reference,
                'business_name' => $request->business_name,
                'business_phone' => $request->business_phone,
                'business_address' => $request->business_address,
                'tax_no' => $request->tax_no,
                'opening_bal' => $request->opening_bal ?? 0,
                'status' => $request->status ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Fetch the created customer data
            $customer = DB::table('customers')->where('id', $insertedId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $html = view('admin.contacts.customers.partials.view-details', compact('customer'))->render();
            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load customer details'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return response()->json(['customer' => $customer]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load customer data'], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $id,  // Changed to nullable
            'phone' => 'required|string|max:20|unique:customers,phone,' . $id,  // Added unique validation with ignore
            'type' => 'required|in:individual,business',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::table('customers')->where('id', $id)->update([
                'name' => $request->name,
                'email' => $request->email,  // Can be null
                'phone' => $request->phone,
                'type' => $request->type,
                'address' => $request->address,
                'reference' => $request->reference,
                'business_name' => $request->business_name,
                'business_phone' => $request->business_phone,
                'business_address' => $request->business_address,
                'tax_no' => $request->tax_no,
                'opening_bal' => $request->opening_bal ?? 0,
                'status' => $request->status ?? 1,
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Customer updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update customer'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('customers')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Customer deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete customer'], 500);
        }
    }

    public function customer_search(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = 10;

        $customers = DB::table('customers')
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->select('id', 'name', 'phone', 'email')
            ->paginate($perPage, ['*'], 'page', $page);

        $results = [];
        foreach ($customers as $customer) {
            $results[] = [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email
            ];
        }

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $customers->hasMorePages()
            ]
        ]);
    }
}
