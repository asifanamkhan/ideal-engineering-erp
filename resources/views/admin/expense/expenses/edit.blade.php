@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .expense-row {
        background-color: #f8f9fc;
        transition: all 0.2s;
    }
    .expense-row:hover {
        background-color: #f1f3f9;
    }
    .remove-expense {
        cursor: pointer;
        color: #e74a3b;
    }
    .remove-expense:hover {
        color: #c0392b;
    }
    .table-calculations {
        background-color: #f8f9fc;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-edit me-2"></i> Edit Expense</h4>
            </div>
            <div>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Expenses
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST" id="expenseForm">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Expense No</label>
                            <input type="text" class="form-control" value="{{ $expense->expense_no }}" readonly disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ $expense->date }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $expense->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $expense->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Narration</label>
                            <textarea name="narration" class="form-control" rows="3" placeholder="Enter narration...">{{ $expense->narration }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Items Section -->
        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i> Expense Items</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Expense Category <span class="text-danger">*</span></label>
                        <select id="category_id" class="form-control select2" style="width: 100%;">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" data-name="{{ $category->name }}">
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                        <input type="number" id="amount" class="form-control" step="0.01" placeholder="Enter amount">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <button type="button" id="addExpenseBtn" class="btn btn-success w-100">
                            <i class="fas fa-plus me-2"></i> Add Expense
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">#</th>
                                <th width="60%">Category</th>
                                <th width="20%">Amount</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="expenseItemsBody">
                            @foreach($expenseDetails as $index => $item)
                            <tr class="expense-row">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $item->category_name }}
                                    <input type="hidden" name="expense_items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <input type="hidden" name="expense_items[{{ $index }}][category_id]" value="{{ $item->expense_category_id }}">
                                    <input type="hidden" name="expense_items[{{ $index }}][amount]" value="{{ $item->amount }}">
                                </td>
                                <td>৳ {{ number_format($item->amount, 2) }}</td>
                                <td class="text-center">
                                    <i class="fas fa-trash-alt remove-expense text-danger" data-index="{{ $index }}" style="cursor: pointer;"></i>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-calculations">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total Amount:</td>
                                <td class="fw-bold" id="totalAmount">{{ number_format($expense->total_amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4 text-center">
            <button type="reset" class="btn btn-lg btn-secondary px-4">
                <i class="fas fa-undo me-2"></i> Reset
            </button>
            <button type="submit" class="btn btn-lg btn-primary px-4" id="updateExpenseBtn">
                <i class="fas fa-save me-2"></i> Update Expense
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#expense-sidebar, #expenses-index-sidebar').addClass('active');
    $('#collapseExpense').addClass('show');

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Category',
        allowClear: true,
        width: '100%'
    });

    let expenseItems = [];
    let rowCounter = {{ count($expenseDetails) }};
    let existingItems = @json($expenseDetails);

    // Load existing items
    $.each(existingItems, function(index, item) {
        expenseItems.push({
            id: item.id,
            category_id: item.expense_category_id,
            category_name: item.category_name,
            amount: parseFloat(item.amount)
        });
    });

    function calculateTotal() {
        var total = 0;
        $.each(expenseItems, function(index, item) {
            total += item.amount;
        });
        $('#totalAmount').text(total.toFixed(2));
    }

    // Add expense item
    $('#addExpenseBtn').click(function() {
        var categoryId = $('#category_id').val();
        var categoryName = $('#category_id option:selected').data('name');
        var amount = parseFloat($('#amount').val());

        if (!categoryId || !amount || amount <= 0) {
            Swal.fire('Error', 'Please select category and enter valid amount', 'error');
            return;
        }

        expenseItems.push({
            id: null,
            category_id: categoryId,
            category_name: categoryName,
            amount: amount
        });

        renderExpenseTable();
        calculateTotal();

        $('#category_id').val('').trigger('change');
        $('#amount').val('');
    });

    function renderExpenseTable() {
        var tbody = $('#expenseItemsBody');
        tbody.empty();

        if (expenseItems.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">No expense items added</td></tr>');
            return;
        }

        $.each(expenseItems, function(index, item) {
            var idField = item.id ? `<input type="hidden" name="expense_items[${index}][id]" value="${item.id}">` : '';
            var row = `
                <tr class="expense-row">
                    <td>${index + 1}</td>
                    <td>
                        ${item.category_name}
                        ${idField}
                        <input type="hidden" name="expense_items[${index}][category_id]" value="${item.category_id}">
                        <input type="hidden" name="expense_items[${index}][amount]" value="${item.amount}">
                    </td>
                    <td>৳ ${item.amount.toFixed(2)}</td>
                    <td class="text-center">
                        <i class="fas fa-trash-alt remove-expense text-danger" data-index="${index}" style="cursor: pointer;"></i>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    $(document).on('click', '.remove-expense', function() {
        var index = $(this).data('index');
        expenseItems.splice(index, 1);
        renderExpenseTable();
        calculateTotal();
    });

    // Initial render
    renderExpenseTable();
    calculateTotal();

    // Form validation before submit
    $('#updateExpenseBtn').click(function(e) {
        e.preventDefault();

        if (expenseItems.length === 0) {
            Swal.fire('Error', 'Please add at least one expense item', 'error');
            return false;
        }

        Swal.fire({
            title: 'Confirm Update',
            text: 'Are you sure you want to update this expense?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#expenseForm').submit();
            }
        });

        return false;
    });
});
</script>
@endsection
