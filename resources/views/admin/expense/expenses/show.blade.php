@extends('layouts.dashboard.app')

@section('css')
<style>
    .info-item {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
    }
    .info-item label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 5px;
        display: block;
    }
    .info-item p {
        margin-bottom: 0;
        font-weight: 600;
        color: #2c3e50;
    }
    .nav-pills .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 0.5rem;
        color: #4a5568;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link i {
        font-size: 0.85rem;
    }
    .nav-pills .nav-link.active {
        background-color: #4e73df;
        color: white;
        box-shadow: 0 2px 5px rgba(78, 115, 223, 0.3);
    }
    .nav-pills .nav-link:not(.active):hover {
        background-color: #e9ecef;
        color: #2c3e50;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-money-bill-wave me-2"></i> Expense Details</h4>
            </div>
            <div>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Expenses
                </a>
            </div>
        </div>
    </div>

    <!-- Header Card -->
    <div class="card shadow mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1 fw-bold">Expense #{{ $expense->expense_no }}</h5>
                    <div class="d-flex gap-3 text-muted small" style="gap: 12px">
                        <span><i class="fas fa-calendar me-1"></i> Date: {{ date('d M, Y', strtotime($expense->date)) }}</span>
                        <span><i class="fas fa-money-bill-wave me-1"></i> Total: ৳ {{ number_format($expense->total_amount, 2) }}</span>
                        <span><i class="fas fa-check-circle me-1"></i> Status:
                            <span class="badge {{ $expense->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                {{ $expense->status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </span>
                    </div>
                </div>
                <div class="d-flex " style="gap: 12px">
                    <button type="button" class="btn btn-sm btn-success print-btn" data-id="{{ $expense->id }}">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <button type="button" class="btn btn-sm btn-primary payment-btn" data-id="{{ $expense->id }}">
                        <i class="fas fa-money-bill-wave me-1"></i> Payment
                    </button>
                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $expense->id }}">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
                {{-- <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="dropdown-item">
                                <i class="fas fa-edit me-2 text-primary"></i> Edit
                            </a>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item payment-btn" data-id="{{ $expense->id }}">
                                <i class="fas fa-money-bill-wave me-2 text-success"></i> Payment
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item delete-btn" data-id="{{ $expense->id }}">
                                <i class="fas fa-trash me-2 text-danger"></i> Delete
                            </button>
                        </li>
                    </ul>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Tab Pills -->
    <div class="mb-3">
        <ul class="nav nav-pills gap-1 flex-nowrap overflow-auto pb-1" style="gap: 0.25rem !important;" id="expenseTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="details-tab" data-bs-toggle="pill" data-bs-target="#details" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i> Details
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="items-tab" data-bs-toggle="pill" data-bs-target="#items" type="button" role="tab">
                    <i class="fas fa-list me-1"></i> Expense Items
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button" role="tab">
                    <i class="fas fa-credit-card me-1"></i> Payment
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Details Tab -->
        <div class="tab-pane fade show active" id="details" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label><i class="fas fa-hashtag me-1"></i> Expense No</label>
                                <p>{{ $expense->expense_no }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label><i class="fas fa-calendar me-1"></i> Expense Date</label>
                                <p>{{ date('d M, Y', strtotime($expense->date)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label><i class="fas fa-money-bill-wave me-1"></i> Total Amount</label>
                                <p>৳ {{ number_format($expense->total_amount, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label><i class="fas fa-credit-card me-1"></i> Paid Amount</label>
                                <p>৳ {{ number_format($expense->paid_amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label><i class="fas fa-chart-line me-1"></i> Payment Status</label>
                                <p>
                                    @php
                                        $status = $expense->payment_status ?? 'unpaid';
                                        $badgeClass = $status == 'paid' ? 'bg-success' : ($status == 'partial_paid' ? 'bg-warning' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label><i class="fas fa-toggle-on me-1"></i> Status</label>
                                <p>
                                    <span class="badge {{ $expense->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $expense->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-item">
                                <label><i class="fas fa-align-left me-1"></i> Narration</label>
                                <p>{{ $expense->narration ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-item">
                                <label><i class="fas fa-user me-1"></i> Created By</label>
                                <p>
                                    @php
                                        $createdUser = DB::table('users')->where('id', $expense->created_by)->first();
                                    @endphp
                                    {{ $createdUser->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Items Tab -->
        <div class="tab-pane fade" id="items" role="tabpanel">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-head">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="60%">Category</th>
                                    <th width="20%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenseDetails as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->category_name }}</td>
                                    <td>৳ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No expense items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="2" class="text-end fw-bold">Total Amount:</th>
                                    <th class="fw-bold">৳ {{ number_format($expense->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Tab -->
        <div class="tab-pane fade" id="payment" role="tabpanel">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Payment Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Amount</h6>
                                    <h4 class="mb-0 text-primary">৳ {{ number_format($expense->total_amount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Paid Amount</h6>
                                    <h4 class="mb-0 text-success">৳ {{ number_format($expense->paid_amount ?? 0, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Due Amount</h6>
                                    <h4 class="mb-0 text-danger">৳ {{ number_format(($expense->total_amount - ($expense->paid_amount ?? 0)), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="payment-history-table-show">
                            <thead class="table-head">
                                <tr>
                                    <th>SL</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Narration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="payment-history-body-show">
                                <tr><td colspan="6" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

@include('admin.partials.payments.payment-section', ['inline' => false])

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('admin.partials.payments.payment-script')

<script>
$(document).ready(function() {
    $('#expense-sidebar, #expenses-index-sidebar').addClass('active');
    $('#collapseExpense').addClass('show');

    var expenseId = {{ $expense->id }};

    // Check if PaymentModal exists
    console.log('PaymentModal:', typeof PaymentModal);

    // Load payment history
    function loadPaymentHistory() {
        $.ajax({
            url: "{{ url('admin/expenses/get-payment-history') }}/" + expenseId,
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(i, p) {
                        html += `<tr>
                            <td>${i+1}</td>
                            <td>${p.payment_date || '-'}</td>
                            <td>৳ ${parseFloat(p.amount).toFixed(2)}</td>
                            <td>${p.payment_mode || '-'}</td>
                            <td>${p.narration || '-'}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button class="custom-dropdown dropdown-item view-payment" data-id="${p.id}"><i class="fas fa-eye me-2 text-info"></i> View</button></li>
                                        <li><button class="custom-dropdown dropdown-item edit-payment" data-id="${p.id}"><i class="fas fa-edit me-2 text-primary"></i> Edit</button></li>
                                        <li><button class="custom-dropdown dropdown-item delete-payment" data-id="${p.id}"><i class="fas fa-trash me-2 text-danger"></i> Delete</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>`;
                    });
                    $('#payment-history-body-show').html(html);
                } else {
                    $('#payment-history-body-show').html('<td><td colspan="6" class="text-center">No payment records found</td></tr>');
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr);
                $('#payment-history-body-show').html('<tr><td colspan="6" class="text-center text-danger">Failed to load payment history</td></tr>');
            }
        });
    }

    loadPaymentHistory();

    // Payment button handler
    $(document).on('click', '.payment-btn', function() {
        console.log('Payment button clicked for expense ID:', expenseId);

        if (typeof PaymentModal !== 'undefined' && PaymentModal.init) {
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
                deleteUrl: "{{ url('admin/expenses/delete-payment') }}",
                modalTitle: 'Expense Payment'
            });
        } else {
            console.error('PaymentModal not found!');
            Swal.fire('Error', 'Payment system not loaded properly', 'error');
        }
    });

    // Delete expense
    var deleteId = {{ $expense->id }};
    $(document).on('click', '.delete-btn', function() {
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: "{{ url('admin/expenses') }}/" + deleteId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    window.location.href = "{{ route('admin.expenses.index') }}";
                } else {
                    Swal.fire('Error!', response.message || 'Failed to delete', 'error');
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                Swal.fire('Error!', 'An error occurred while deleting.', 'error');
            }
        });
    });

    // Refresh payment history after payment success
    $(document).on('paymentSuccess', function() {
        loadPaymentHistory();
        location.reload();
    });
});
</script>
@endsection
