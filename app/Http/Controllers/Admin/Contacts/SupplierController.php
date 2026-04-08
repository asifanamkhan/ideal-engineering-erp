<?php

namespace App\Http\Controllers\Admin\Contacts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $suppliers = DB::table('suppliers')
                ->leftJoin('branches', 'suppliers.branch_id', '=', 'branches.id')
                ->select(['suppliers.*', 'branches.name as branch'])
                ->orderBy('suppliers.id', 'desc');

            // Apply search filter if search term exists
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];
                $suppliers->where(function ($query) use ($searchTerm) {
                    $query->where('suppliers.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('suppliers.email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('suppliers.phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('suppliers.supplier_id', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('suppliers.address', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('branches.name', 'LIKE', "%{$searchTerm}%");
                });
            }

            return DataTables::of($suppliers)
                ->addIndexColumn()
                ->addColumn('supplier_id', function ($supplier) {
                    return '<span class="badge bg-info">' . ($supplier->supplier_id ?? 'N/A') . '</span>';
                })
                ->addColumn('name', function ($supplier) {
                    return '<strong>' . e($supplier->name) . '</strong>';
                })
                ->addColumn('phone', function ($supplier) {
                    return $supplier->phone ?? 'N/A';
                })
                ->addColumn('type', function ($supplier) {
                    $type = $supplier->type ?? 'individual';
                    $badgeClass = $type == 'business' ? 'bg-primary' : 'bg-success';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($type) . '</span>';
                })
                ->addColumn('status_badge', function ($supplier) {
                    $status = $supplier->status ?? 1;
                    if ($status == 1 || $status == 'active' || $status == 'Active') {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('action', function ($supplier) {
                    return view('admin.contacts.suppliers.partials.action-btn-view', ['id' => $supplier->id])->render();
                })
                ->rawColumns(['supplier_id', 'name', 'type', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.contacts.suppliers.index');
    }

    public function getForm()
    {
        return view('admin.contacts.suppliers.partials.modal-form');
    }

    private function generateSupplierId()
    {
        // Get the last supplier ID
        $lastSupplier = DB::table('suppliers')
            ->where('supplier_id', 'LIKE', 'SUP%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSupplier) {
            $lastNumber = intval(substr($lastSupplier->supplier_id, 3)); // Changed from 4 to 3 for 'SUP'
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $formattedNumber = str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        return 'SUP' . $formattedNumber;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'required|string|max:20|unique:suppliers,phone',
            'type' => 'required|in:individual,business',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $supplierId = $this->generateSupplierId();

            DB::table('suppliers')->insert([
                'supplier_id' => $supplierId,
                'branch_id' => 1,
                'name' => $request->name,
                'email' => $request->email,
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

            return response()->json(['success' => true, 'message' => 'Supplier created successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create supplier: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $supplier = DB::table('suppliers')->where('id', $id)->first();

            if (!$supplier) {
                return response()->json(['error' => 'Supplier not found'], 404);
            }

            $html = view('admin.contacts.suppliers.partials.view-details', compact('supplier'))->render();
            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load supplier details'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $supplier = DB::table('suppliers')->where('id', $id)->first();

            if (!$supplier) {
                return response()->json(['error' => 'Supplier not found'], 404);
            }

            return response()->json(['supplier' => $supplier]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load supplier data'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $id,
            'phone' => 'required|string|max:20|unique:suppliers,phone,' . $id,
            'type' => 'required|in:individual,business',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
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
            ];

            DB::table('suppliers')->where('id', $id)->update($updateData);

            return response()->json(['success' => true, 'message' => 'Supplier updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update supplier: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('suppliers')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Supplier deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete supplier'], 500);
        }
    }
}