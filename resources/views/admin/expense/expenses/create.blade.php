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
                <h4 class="mb-1"><i class="fas fa-plus-circle me-2"></i> Add New Expense</h4>
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

    <form action="{{ route('admin.expenses.store') }}" method="POST" id="expenseForm">
        @csrf

        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 row">
                        <div class="col-md-12">
                            <div class="">
                                <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Narration</label>
                            <textarea name="narration" class="form-control" rows="4" placeholder="Enter narration..."></textarea>
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
                            <tr>
                                <td colspan="4" class="text-center text-muted">No expense items added</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-calculations">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total Amount:</td>
                                <td class="fw-bold" id="totalAmount">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Section (Using Partial) -->
        <div id="paymentSectionContainer" class="mt-4">
            @include('admin.partials.payments.payment-section', ['inline' => true])
        </div>

        <div class="mt-4 mb-4 text-center">
            <button type="reset" class="btn btn-lg btn-secondary px-4">
                <i class="fas fa-undo me-2"></i> Reset
            </button>
            <button type="submit" class="btn btn-lg btn-success px-4" id="saveExpenseBtn">
                <i class="fas fa-save me-2"></i> Save Expense
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('admin.partials.payments.payment-script')

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
    let rowCounter = 0;

    // Calculate total from expense items
    function calculateTotal() {
        var total = 0;
        $.each(expenseItems, function(index, item) {
            total += item.amount;
        });
        $('#totalAmount').text(total.toFixed(2));

        // Update payment section total amount
        $('#payment_total_amount').text(total.toFixed(2));
        $('#due_amount').text(total.toFixed(2));
        $('#due_amount').data('raw', total);
        $('#max_due').text(total.toFixed(2));
        $('#payment_amount').attr('max', total);

        var paidAmount = parseFloat($('#payment_amount').val()) || 0;
        var dueAmount = total - paidAmount;
        $('#payment_due_amount').text(dueAmount.toFixed(2));
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
            temp_id: rowCounter++,
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
            var row = `
                <tr class="expense-row">
                    <td>${index + 1}</td>
                    <td>
                        ${item.category_name}
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

    // Payment amount change handler
    $('#payment_amount').on('input', function() {
        var totalAmount = parseFloat($('#totalAmount').text()) || 0;
        var paidAmount = parseFloat($(this).val()) || 0;
        var dueAmount = totalAmount - paidAmount;

        $('#payment_paid_amount').text(paidAmount.toFixed(2));
        $('#payment_due_amount').text(dueAmount.toFixed(2));
    });

    // Form validation before submit
    $('#saveExpenseBtn').click(function(e) {
        e.preventDefault();

        if (expenseItems.length === 0) {
            Swal.fire('Error', 'Please add at least one expense item', 'error');
            return false;
        }

        var paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        var totalAmount = parseFloat($('#totalAmount').text()) || 0;

        if (paymentAmount > totalAmount) {
            Swal.fire('Error', 'Payment amount cannot exceed total amount', 'error');
            return false;
        }

        // Confirm message
        var confirmMessage = `Total Amount: ৳ ${totalAmount.toFixed(2)}\n`;
        if (paymentAmount > 0) {
            confirmMessage += `Payment Amount: ৳ ${paymentAmount.toFixed(2)}\n`;
            confirmMessage += `Due Amount: ৳ ${(totalAmount - paymentAmount).toFixed(2)}`;
        }

        Swal.fire({
            title: 'Confirm Save',
            html: `Do you want to save this expense?<br><br><strong>${confirmMessage.replace(/\n/g, '<br>')}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Save it!',
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
