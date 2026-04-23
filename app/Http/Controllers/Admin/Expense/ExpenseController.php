<?php

namespace App\Http\Controllers\Admin\Expense;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\PaymentHelper;

class ExpenseController extends Controller
{
    protected $paymentHelper;
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = DB::table('expenses')
                ->select(
                    'id',
                    'expense_no',
                    'date',
                    'total_amount',
                    'paid_amount',
                    'payment_status',
                    'status',
                    'narration',
                    'created_at'
                )
                ->orderBy('id', 'desc');

            return DataTables::of($expenses)
                ->addIndexColumn()
                ->addColumn('expense_no', function ($expense) {
                    return $expense->expense_no;
                })
                ->addColumn('date', function ($expense) {
                    return date('d-m-Y', strtotime($expense->date));
                })
                ->addColumn('total_amount', function ($expense) {
                    return '৳ ' . number_format($expense->total_amount, 2);
                })
                ->addColumn('paid_amount', function ($expense) {
                    $paid = $expense->paid_amount ?? 0;
                    return '৳ ' . number_format($paid, 2);
                })
                ->addColumn('payment_status_badge', function ($expense) {
                    $status = $expense->payment_status ?? 'unpaid';
                    $badges = [
                        'paid' => '<span class="badge bg-success">Paid</span>',
                        'partial_paid' => '<span class="badge bg-warning">Partial</span>',
                        'unpaid' => '<span class="badge bg-danger">Unpaid</span>'
                    ];
                    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
                })
                ->addColumn('narration', function ($expense) {
                    $narration = $expense->narration ?? '-';
                    if (strlen($narration) > 70) {
                        return '<span class="narration-text" title="' . e($narration) . '">' . e(substr($narration, 0, 50)) . '...</span>';
                    }
                    return '<span class="narration-text">' . e($narration) . '</span>';
                })
                ->addColumn('action', function ($expense) {
                    return view('admin.expense.expenses.partials.action-btn-view', ['id' => $expense->id])->render();
                })
                ->rawColumns(['payment_status_badge', 'narration', 'action'])
                ->make(true);
        }

        return view('admin.expense.expenses.index');
    }

    public function create()
    {
        $categories = DB::table('expense_categories')->where('status', 1)->get();
        return view('admin.expense.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'narration' => 'nullable|string',
            'status' => 'required|in:0,1',
            'expense_items' => 'required|array|min:1',
            'expense_items.*.category_id' => 'required|exists:expense_categories,id',
            'expense_items.*.amount' => 'required|numeric|min:0.01',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_mode_id' => 'required_if:payment_amount,>0|exists:acc_payment_modes,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate Expense No
            $lastExpense = DB::table('expenses')->orderBy('id', 'desc')->first();
            $lastId = $lastExpense ? $lastExpense->id : 0;
            $expenseNo = 'EXP-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            // Calculate total amount
            $totalAmount = array_sum(array_column($request->expense_items, 'amount'));

            // Insert into expenses
            $expenseId = DB::table('expenses')->insertGetId([
                'expense_no' => $expenseNo,
                'date' => $request->date,
                'branch_id' => auth()->user()->branch_id ?? 1,
                'total_amount' => $totalAmount,
                'status' => $request->status,
                'narration' => $request->narration,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert into expense_details
            foreach ($request->expense_items as $item) {
                DB::table('expense_details')->insert([
                    'expense_id' => $expenseId,
                    'expense_category_id' => $item['category_id'],
                    'amount' => $item['amount'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Process payment if any
            if ($request->payment_amount && $request->payment_amount > 0) {
                $additionalData = [
                    'narration' => $request->payment_narration,
                    'chq_no' => $request->chq_no,
                    'chq_date' => $request->chq_date,
                    'card_no' => $request->card_no,
                    'online_trx_id' => $request->online_trx_id,
                    'online_trx_dt' => $request->online_trx_dt,
                    'mfs_name' => $request->mfs_name,
                    'bank_code' => $request->bank_code,
                    'bank_ac_no' => $request->bank_ac_no,
                ];

                $entityData = [
                    'table' => 'expenses',
                    'amount_field' => 'total_amount',
                    // 'discount_field' => 'discount',
                    'paid_field' => 'paid_amount',
                    'status_field' => 'payment_status'
                ];

                $result = $this->paymentHelper->processPayment(
                    'supplier',
                    null,        // null ই থাকবে
                    'expense',
                    $expenseId,
                    $request->payment_amount,
                    $request->payment_mode_id,
                    $additionalData,
                    $entityData
                );

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
            }

            DB::commit();

            return redirect()->route('admin.expenses.index')
                ->with('success', 'Expense created successfully! Expense No: ' . $expenseNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create expense: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'narration' => 'nullable|string',
            'status' => 'required|in:0,1',
            'expense_items' => 'required|array|min:1',
            'expense_items.*.category_id' => 'required|exists:expense_categories,id',
            'expense_items.*.amount' => 'required|numeric|min:0.01',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_mode_id' => 'required_if:payment_amount,>0|exists:acc_payment_modes,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $expense = DB::table('expenses')->where('id', $id)->first();
        if (!$expense) {
            return redirect()->route('admin.expenses.index')->with('error', 'Expense not found');
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = array_sum(array_column($request->expense_items, 'amount'));

            // Update expenses
            DB::table('expenses')->where('id', $id)->update([
                'date' => $request->date,
                'total_amount' => $totalAmount,
                'status' => $request->status,
                'narration' => $request->narration,
                'updated_at' => now()
            ]);

            // Get existing detail IDs
            $existingIds = [];
            foreach ($request->expense_items as $item) {
                if (isset($item['id']) && !empty($item['id'])) {
                    // Update existing
                    DB::table('expense_details')->where('id', $item['id'])->update([
                        'expense_category_id' => $item['category_id'],
                        'amount' => $item['amount'],
                        'updated_at' => now()
                    ]);
                    $existingIds[] = $item['id'];
                } else {
                    // Insert new
                    $newId = DB::table('expense_details')->insertGetId([
                        'expense_id' => $id,
                        'expense_category_id' => $item['category_id'],
                        'amount' => $item['amount'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $existingIds[] = $newId;
                }
            }

            // Delete removed items
            DB::table('expense_details')
                ->where('expense_id', $id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            DB::commit();

            return redirect()->route('admin.expenses.index')
                ->with('success', 'Expense updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update expense: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $expense = DB::table('expenses')->where('id', $id)->first();
        if (!$expense) {
            return redirect()->route('admin.expenses.index')->with('error', 'Expense not found');
        }

        $expenseDetails = DB::table('expense_details')
            ->leftJoin('expense_categories', 'expense_details.expense_category_id', '=', 'expense_categories.id')
            ->where('expense_details.expense_id', $id)
            ->select('expense_details.*', 'expense_categories.name as category_name')
            ->get();

        return view('admin.expense.expenses.show', compact('expense', 'expenseDetails'));
    }

    public function edit($id)
    {
        $expense = DB::table('expenses')->where('id', $id)->first();
        if (!$expense) {
            return redirect()->route('admin.expenses.index')->with('error', 'Expense not found');
        }

        $expenseDetails = DB::table('expense_details')
            ->leftJoin('expense_categories', 'expense_details.expense_category_id', '=', 'expense_categories.id')
            ->where('expense_details.expense_id', $id)
            ->select('expense_details.*', 'expense_categories.name as category_name')
            ->get();

        $categories = DB::table('expense_categories')->where('status', 1)->get();

        return view('admin.expense.expenses.edit', compact('expense', 'expenseDetails', 'categories'));
    }
    public function getPaymentHistory($typeId)
    {
        $result = $this->paymentHelper->getPaymentHistory('expense', $typeId);
        return response()->json($result);
    }

    public function getPaymentDetails($id)
    {
        $entityData = [
            'table' => 'expenses',
            'amount_field' => 'total_amount',
            'paid_field' => 'paid_amount',
            'status_field' => 'payment_status'
        ];

        $result = $this->paymentHelper->getPaymentDetails('expense', $id, $entityData);
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
            $validationRules['type_id'] = 'required|exists:expenses,id';

            $request->validate($validationRules);

            $expense = DB::table('expenses')->where('id', $request->type_id)->first();
            if (!$expense) {
                return response()->json(['success' => false, 'message' => 'Expense not found'], 404);
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
                'table' => 'expenses',
                'amount_field' => 'total_amount',
                // 'discount_field' => 'discount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
            ];

            $result = $this->paymentHelper->processPayment(
                'supplier',
                null,
                'expense',
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
                'table' => 'expenses',
                'amount_field' => 'total_amount',
                // 'discount_field' => 'discount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
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
                'type_id' => 'required|exists:expenses,id',
            ]);

            $entityData = [
                'table' => 'expenses',
                'amount_field' => 'total_amount',
                'paid_field' => 'paid_amount',
                'status_field' => 'payment_status'
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
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // First delete expense details
            DB::table('expense_details')->where('expense_id', $id)->delete();

            // Then delete expense
            DB::table('expenses')->where('id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Expense deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete expense: ' . $e->getMessage()], 500);
        }
    }
    public function printExpense(Request $request)
{
    try {
        $expenseId = $request->expense_id;
        $documents = $request->documents;

        // Fetch expense data
        $expense = DB::table('expenses')->where('id', $expenseId)->first();
        if (!$expense) {
            throw new \Exception('Expense not found');
        }

        // Fetch expense items with categories
        $expenseItems = DB::table('expense_details')
            ->leftJoin('expense_categories', 'expense_details.expense_category_id', '=', 'expense_categories.id')
            ->where('expense_details.expense_id', $expenseId)
            ->select('expense_details.*', 'expense_categories.name as category_name')
            ->get();

        // Prepare expense data
        $expenseData = (object) [
            'id' => $expense->id,
            'expense_no' => $expense->expense_no,
            'date' => $expense->date,
            'total_amount' => $expense->total_amount,
            'paid_amount' => $expense->paid_amount ?? 0,
            'status' => $expense->status,
            'narration' => $expense->narration,
            'items' => $expenseItems
        ];

        // Generate HTML for print
        $html = view('admin.expense.expenses.prints.print-layout', compact('expenseData', 'documents'))->render();

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
