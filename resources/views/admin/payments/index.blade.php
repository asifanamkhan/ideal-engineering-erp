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

    .dynamic-fields {
        margin-top: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-credit-card me-2"></i>
                    Payment Transactions
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / Payments</span>
            <div>
                <a href="#" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-user me-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="payments-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Date</th>
                            <th width="12%">Transaction ID</th>
                            <th width="15%">Payment For</th>
                            <th width="12%">Type</th>
                            <th width="10%">Amount</th>
                            <th width="8%">Method</th>
                            <th width="15%">Narration</th>
                            <th width="10%">Actions</th>
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

<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Payment Details</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsBody">Loading...</div>
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
                Are you sure you want to delete this payment? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Payment</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPaymentForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_payment_id" name="payment_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="edit_amount" class="form-control" step="0.01"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="edit_payment_date" class="form-control" required>
                        </div>
                    </div>

                    <!-- Payment Mode Select -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode_id" id="edit_payment_mode_id" class="form-control" required>
                            <option value="">Select Payment Mode</option>
                            @php
                            $paymentModes = DB::table('acc_payment_modes')->get();
                            @endphp
                            @foreach($paymentModes as $mode)
                            <option value="{{ $mode->id }}">{{ $mode->mode_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dynamic Fields (Cheque, Card, etc.) -->
                    <div id="edit_cash_fields" class="dynamic-fields" style="display: none;">
                        <div class="alert alert-info">Cash payment selected. No additional information needed.</div>
                    </div>

                    <div id="edit_cheque_fields" class="dynamic-fields" style="display: none;">
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning">Cheque Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6"><input type="text" name="chq_no" id="edit_chq_no"
                                            class="form-control" placeholder="Cheque No"></div>
                                    <div class="col-md-6"><input type="date" name="chq_date" id="edit_chq_date"
                                            class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="edit_card_fields" class="dynamic-fields" style="display: none;">
                        <div class="card border-info mb-3">
                            <div class="card-header bg-info">Card Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4"><input type="text" name="card_no" id="edit_card_no"
                                            class="form-control" placeholder="Card No"></div>
                                    <div class="col-md-4"><input type="text" name="online_trx_id"
                                            id="edit_online_trx_id" class="form-control" placeholder="Transaction ID">
                                    </div>
                                    <div class="col-md-4"><input type="date" name="online_trx_dt"
                                            id="edit_online_trx_dt" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="edit_mobile_banking_fields" class="dynamic-fields" style="display: none;">
                        <div class="card border-success mb-3">
                            <div class="card-header bg-success">Mobile Banking</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <select name="mfs_name" id="edit_mfs_name" class="form-control">
                                            <option value="">Select MFS</option>
                                            <option value="Bkash">Bkash</option>
                                            <option value="Nagad">Nagad</option>
                                            <option value="Rocket">Rocket</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4"><input type="text" name="online_trx_id"
                                            id="edit_mobile_trx_id" class="form-control" placeholder="Transaction ID">
                                    </div>
                                    <div class="col-md-4"><input type="date" name="online_trx_dt"
                                            id="edit_mobile_trx_dt" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="edit_internet_banking_fields" class="dynamic-fields" style="display: none;">
                        <div class="card border-secondary mb-3">
                            <div class="card-header bg-secondary">Internet Banking</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <select name="bank_code" id="edit_bank_code" class="form-control">
                                            <option value="">Select Bank</option>
                                            @php
                                            $bankInfos = DB::table('acc_bank_info')->get();
                                            @endphp
                                            @foreach($bankInfos as $bank)
                                            <option value="{{ $bank->bank_code }}">{{ $bank->bank_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3"><input type="text" name="bank_ac_no" id="edit_bank_ac_no"
                                            class="form-control" placeholder="Account No"></div>
                                    <div class="col-md-3"><input type="text" name="online_trx_id"
                                            id="edit_internet_trx_id" class="form-control" placeholder="Transaction ID">
                                    </div>
                                    <div class="col-md-3"><input type="date" name="online_trx_dt"
                                            id="edit_internet_trx_dt" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="edit_giftcard_fields" class="dynamic-fields" style="display: none;">
                        <div class="alert alert-secondary">Gift Card/Points selected.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Narration</label>
                        <textarea name="narration" id="edit_narration" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updatePaymentBtn">
                        <i class="fas fa-save me-2"></i> Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#payments-sidebar, #payments-index-sidebar').addClass('active');
    $('#collapsePayments').addClass('show');

    var table = $('#payments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.payments.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'payment_date', orderable: true, searchable: true },
            { data: 'tran_id', name: 'tran_id', orderable: true, searchable: true },
            { data: 'payment_for_display', name: 'payment_for', orderable: true, searchable: true },
            { data: 'type_display', name: 'type', orderable: true, searchable: true },
            { data: 'amount_display', name: 'amount', orderable: true, searchable: false },
            { data: 'payment_mode', name: 'payment_mode', orderable: true, searchable: true },
            { data: 'narration', name: 'narration', orderable: false, searchable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
        responsive: true
    });

    // View payment
    $(document).on('click', '.view-payment', function() {
        var id = $(this).data('id');
        $.ajax({
            url: "{{ url('admin/payments') }}/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var p = response.data;
                    var html = '<table class="table table-bordered">';
                    html += '<tr><th width="40%">Date</th><td>' + (p.payment_date || '-') + 'NonNull</td></tr>';
                    html += '<tr><th>Amount</th><td>' + (p.dr_cr == 'DR' ? '+' : '-') + ' ৳ ' + parseFloat(p.amount).toFixed(2) + 'NonNull</td></tr>';
                    html += '<tr><th>Payment For</th><td>' + (p.payment_for || '-') + (p.payment_for_id ? ' (ID: ' + p.payment_for_id + ')' : '') + 'NonNull</td></tr>';
                    html += '<tr><th>Type</th><td>' + (p.type || '-') + (p.type_id ? ' (ID: ' + p.type_id + ')' : '') + 'NonNullNonNull</td></tr>';
                    html += '<tr><th>Payment Mode</th><td>' + (p.payment_mode || '-') + 'NonNullNonNull</td></tr>';
                    html += '<tr><th>Narration</th><td>' + (p.narration || '-') + 'NonNullNonNull</td></tr>';
                    if (p.chq_no) html += '<tr><th>Cheque No</th><td>' + p.chq_no + 'NonNullNonNull</td></tr>';
                    if (p.chq_date) html += '<tr><th>Cheque Date</th><td>' + p.chq_date + 'NonNullNonNull</td></tr>';
                    if (p.card_no) html += '<tr><th>Card No</th><td>' + p.card_no + 'NonNullNonNull</td></tr>';
                    if (p.online_trx_id) html += '<td><th>Transaction ID</th><td>' + p.online_trx_id + 'NonNullNonNull</td></tr>';
                    if (p.mfs_name) html += '<tr><th>MFS Name</th><td>' + p.mfs_name + 'NonNullNonNull</td></tr>';
                    if (p.bank_code) html += '<tr><th>Bank Code</th><td>' + p.bank_code + 'NonNullNonNull</td></tr>';
                    if (p.bank_ac_no) html += '<td><th>Bank Account</th><td>' + p.bank_ac_no + 'NonNullNonNull</td></tr>';
                    html += '</table>';
                    $('#paymentDetailsBody').html(html);
                    $('#viewPaymentModal').modal('show');
                }
            }
        });
    });

    // Delete payment
    var deleteId;
    $(document).on('click', '.delete-payment', function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: "{{ url('admin/payments') }}/" + deleteId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message });
            },
            error: function() {
                $('#deleteModal').modal('hide');
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete payment' });
            }
        });
    });

    // Edit payment - Load data to modal
    $(document).on('click', '.edit-payment', function() {
        var id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/payments') }}/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var p = response.data;

                    // Basic fields
                    $('#edit_payment_id').val(p.id);
                    $('#edit_amount').val(p.amount);
                    $('#edit_payment_date').val(p.payment_date);
                    $('#edit_narration').val(p.narration);

                    // Payment mode
                    $('#edit_payment_mode_id').val(p.pay_method_id).trigger('change');

                    // Dynamic fields based on payment mode
                    var modeName = $('#edit_payment_mode_id option:selected').text();

                    // Hide all dynamic fields first
                    $('.dynamic-fields').hide();

                    // Load mode-specific fields
                    if (modeName == 'Cheque') {
                        $('#edit_cheque_fields').show();
                        $('#edit_chq_no').val(p.chq_no || '');
                        $('#edit_chq_date').val(p.chq_date || '');
                    } else if (modeName == 'Card') {
                        $('#edit_card_fields').show();
                        $('#edit_card_no').val(p.card_no || '');
                        $('#edit_online_trx_id').val(p.online_trx_id || '');
                        $('#edit_online_trx_dt').val(p.online_trx_dt || '');
                    } else if (modeName == 'Mobile Banking') {
                        $('#edit_mobile_banking_fields').show();
                        $('#edit_mfs_name').val(p.mfs_name || '');
                        $('#edit_mobile_trx_id').val(p.online_trx_id || '');
                        $('#edit_mobile_trx_dt').val(p.online_trx_dt || '');
                    } else if (modeName == 'Internet Banking') {
                        $('#edit_internet_banking_fields').show();
                        $('#edit_bank_code').val(p.bank_code || '');
                        $('#edit_bank_ac_no').val(p.bank_ac_no || '');
                        $('#edit_internet_trx_id').val(p.online_trx_id || '');
                        $('#edit_internet_trx_dt').val(p.online_trx_dt || '');
                    } else if (modeName == 'Cash') {
                        $('#edit_cash_fields').show();
                    } else if (modeName == 'Gift Card' || modeName == 'Points') {
                        $('#edit_giftcard_fields').show();
                    }

                    $('#editPaymentModal').modal('show');
                }
            }
        });
    });

    // Payment mode change handler for edit modal
    $(document).on('change', '#edit_payment_mode_id', function() {
        var modeName = $(this).find('option:selected').text();

        // Hide all dynamic fields
        $('#edit_cash_fields, #edit_cheque_fields, #edit_card_fields, #edit_mobile_banking_fields, #edit_internet_banking_fields, #edit_giftcard_fields').hide();

        // Show relevant fields
        switch(modeName) {
            case 'Cash':
                $('#edit_cash_fields').show();
                break;
            case 'Cheque':
                $('#edit_cheque_fields').show();
                break;
            case 'Card':
                $('#edit_card_fields').show();
                break;
            case 'Mobile Banking':
                $('#edit_mobile_banking_fields').show();
                break;
            case 'Internet Banking':
                $('#edit_internet_banking_fields').show();
                break;
            case 'Gift Card':
            case 'Points':
                $('#edit_giftcard_fields').show();
                break;
            default:
                break;
        }
    });

    // Update payment
    $('#editPaymentForm').on('submit', function(e) {
        e.preventDefault();

        var id = $('#edit_payment_id').val();
        var formData = $(this).serialize();

        $('#updatePaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: "{{ url('admin/payments') }}/" + id,
            type: 'POST',
            data: formData + '&_method=PUT&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#editPaymentModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update payment' });
            },
            complete: function() {
                $('#updatePaymentBtn').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Update Payment');
            }
        });
    });

    // Print receipt
$(document).on('click', '.print-receipt', function() {
    var id = $(this).data('id');

    Swal.fire({
        title: 'Preparing receipt...',
        text: 'Please wait while we prepare your receipt.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ url('admin/payments/print-receipt') }}/" + id,
        type: 'GET',
        success: function(response) {
            Swal.close();
            if (response.success && response.html) {
                var printTab = window.open();
                printTab.document.write(response.html);
                printTab.document.close();
            } else {
                Swal.fire({ icon: 'error', title: 'Error!', text: response.message || 'Failed to generate receipt.' });
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error!', text: 'An error occurred while preparing receipt.' });
        }
    });
});
</script>
@endsection
