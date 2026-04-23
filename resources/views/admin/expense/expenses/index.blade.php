@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .table td {
        vertical-align: middle;
    }

    .narration-text {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .text-end {
        text-align: end;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Expense List
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / Expenses</span>
            <div>
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-plus"></i> Add New Expense
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="expenses-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Expense No</th>
                            <th width="10%">Date</th>
                            <th width="20%">Narration</th>
                            <th width="12%" class="text-end">Amount</th>
                            <th width="12%" class="text-end">Paid</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this expense? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@include('admin.partials.payments.payment-section', ['inline' => false])

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('admin.partials.payments.payment-script')

<script>
    // Activate sidebar
    $('#expense-sidebar, #expenses-index-sidebar').addClass('active');
    $('#collapseExpense').addClass('show');

    var table = $('#expenses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.expenses.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'expense_no', name: 'expense_no', orderable: true, searchable: true },
            { data: 'date', name: 'date', orderable: true, searchable: true },
            { data: 'narration', name: 'narration', orderable: false, searchable: true },
            { data: 'total_amount', name: 'total_amount', orderable: true, searchable: false, className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', orderable: true, searchable: false, className: 'text-end' },
            { data: 'payment_status_badge', name: 'payment_status', orderable: true, searchable: true, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search expenses...",
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No expenses found",
            info: "Showing _START_ to _END_ of _TOTAL_ expenses",
            infoEmpty: "No expenses available",
            infoFiltered: "(filtered from _MAX_ total expenses)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        }
    });

    var deleteId;
    $(document).on('click', '.delete-btn', function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: "{{ url('admin/expenses') }}/" + deleteId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message });
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete expense' });
            }
        });
    });

    // Payment button handler
    $(document).on('click', '.payment-btn', function() {
        var expenseId = $(this).data('id');

        PaymentModal.init({
            paymentFor: 'supplier',
            paymentForId: null,
            type: 'expense',
            typeId: expenseId,
            getDetailsUrl: "{{ url('admin/expenses/get-payment-details') }}/" + expenseId,
            processUrl: "{{ url('admin/expenses/process-payment') }}",
            updateUrl: "{{ url('admin/expenses/update-payment') }}",
            getHistoryUrl: "{{ url('admin/expenses/get-payment-history') }}",
            getPaymentUrl: "{{ url('admin/expenses/get-payment') }}",
            deleteUrl: "{{ url('admin/expenses/delete-payment') }}",  // এই URL ঠিক আছে
            modalTitle: 'Expense Payment'
        });
    });

    // ========== Refresh DataTable after payment ==========
    $(document).on('paymentSuccess', function() {
        table.ajax.reload();
    });
    // ===================================================

    // Print Expense
$(document).on('click', '.print-expense-btn', function() {
    var expenseId = $(this).data('id');

    Swal.fire({
        title: 'Preparing expense voucher...',
        text: 'Please wait while we prepare your document.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ url('admin/expenses/print') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            expense_id: expenseId,
            documents: ['expense']
        },
        success: function(response) {
            Swal.close();
            if (response.success && response.html) {
                var printTab = window.open();
                printTab.document.write(response.html);
                printTab.document.close();
            } else {
                Swal.fire({ icon: 'error', title: 'Error!', text: response.message || 'Failed to generate expense voucher.' });
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'An error occurred.' });
        }
    });
});
</script>
@endsection
