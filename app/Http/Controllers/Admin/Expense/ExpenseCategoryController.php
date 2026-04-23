<?php

namespace App\Http\Controllers\Admin\Expense;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expense_category = DB::table('expense_categories')
                ->select('*')->orderBy('id', 'desc');

            return DataTables::of($expense_category)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($unit) {
                    return $unit->id;
                })
                ->addColumn('status_badge', function ($unit) {
                    $status = $unit->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($unit) {
                    return view('admin.expense.expense-category.partials.action-btn-view', ['id' => $unit->id])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.expense.expense-category.index');
    }

    public function create()
    {
        return view('admin.expense.expense-category.partials.create-modal');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191', 'unique:expense_categories'],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 1;
        $data['created_by'] = Auth::user()->id;

        ExpenseCategory::create($data);

        return response()->json(['success' => true, 'message' => 'Expense Category created successfully!']);
    }

    public function edit($id)
    {
        try {
            $unit = ExpenseCategory::findOrFail($id);
            // Return JSON data instead of view
            return response()->json([
                'id' => $unit->id,
                'name' => $unit->name,
                'status' => $unit->status,
                'description' => $unit->description
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'ExpenseCategory not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('expense_categories')->ignore($id)
            ],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $unit = ExpenseCategory::findOrFail($id);
            $data = $request->all();
            $data['status'] = $request->status ?? $unit->status;

            $unit->update($data);

            return response()->json(['success' => true, 'message' => 'Expense Category updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update expense categories'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $unit = ExpenseCategory::findOrFail($id);
            $unit->delete();

            return response()->json(['success' => true, 'message' => 'Expense Category deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete expense_categories'], 500);
        }
    }
}
