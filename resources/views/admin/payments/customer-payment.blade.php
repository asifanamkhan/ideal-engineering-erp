@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .job-row {
        background-color: #f8f9fc;
        transition: all 0.2s;
    }
    .job-row:hover {
        background-color: #f1f3f9;
    }
    .payment-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    .payment-card-header {
        background: #f8f9fc;
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .dynamic-fields {
        margin-top: 15px;
    }
    .summary-card {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .summary-label {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    .summary-value {
        font-size: 18px;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-money-bill-wave me-2"></i> Customer Payment</h4>
            </div>
            <div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Payments
                </a>
            </div>
        </div>
    </div>

    <form id="paymentForm">
        @csrf

        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                            <select id="customer_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">Search customer...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Type <span class="text-danger">*</span></label>
                            <select id="payment_type" class="form-control" required>
                                <option value="random">Random Payment</option>
                                <option value="job">Job Wise Payment</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Random Payment Section -->
        <div id="randomPaymentSection" class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-random me-2"></i> Random Payment</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                            <input type="number" id="random_amount" class="form-control" step="0.01" placeholder="Enter amount">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Wise Payment Section -->
        <div id="jobPaymentSection" class="card shadow mt-4" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-briefcase me-2"></i> Job Wise Payment</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="jobsTable">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">SL</th>
                                <th width="15%">Job ID</th>
                                <th width="10%">Date</th>
                                <th width="15%">Engine</th>
                                <th width="12%">Total</th>
                                <th width="12%">Paid</th>
                                <th width="12%">Due</th>
                                <th width="12%">Pay Amount</th>
                            </tr>
                        </thead>
                        <tbody id="jobsTableBody">
                            <tr><td colspan="8" class="text-center text-muted">Select a customer first</td></tr>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="4" class="text-end fw-bold">Total:</th>
                                <th id="total_total" class="fw-bold">0.00</th>
                                <th id="total_paid" class="fw-bold">0.00</th>
                                <th id="total_due" class="fw-bold">0.00</th>
                                <th id="total_pay_amount" class="fw-bold text-success">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Method Section -->
        <div class="payment-card mt-4">
            <div class="payment-card-header">
                <i class="fas fa-credit-card me-2 text-primary"></i> Payment Method
            </div>
            <div class="p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode_id" id="payment_mode_id" class="form-control" required>
                            <option value="">Select Payment Mode</option>
                            @foreach($paymentModes as $mode)
                            <option value="{{ $mode->id }}">{{ $mode->mode_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Narration</label>
                        <textarea name="narration" id="narration" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <!-- Dynamic Payment Fields -->
                <div id="cash_fields" class="dynamic-fields" style="display: none;">
                    <div class="alert alert-info">Cash payment selected. No additional information needed.</div>
                </div>

                <div id="cheque_fields" class="dynamic-fields" style="display: none;">
                    <div class="card border-warning">
                        <div class="card-header bg-warning">Cheque Information</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6"><input type="text" name="chq_no" class="form-control" placeholder="Cheque No"></div>
                                <div class="col-md-6"><input type="date" name="chq_date" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="card_fields" class="dynamic-fields" style="display: none;">
                    <div class="card border-info">
                        <div class="card-header bg-info">Card Information</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4"><input type="text" name="card_no" class="form-control" placeholder="Card No"></div>
                                <div class="col-md-4"><input type="text" name="online_trx_id" class="form-control" placeholder="Transaction ID"></div>
                                <div class="col-md-4"><input type="date" name="online_trx_dt" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="mobile_banking_fields" class="dynamic-fields" style="display: none;">
                    <div class="card border-success">
                        <div class="card-header bg-success">Mobile Banking</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="mfs_name" class="form-control">
                                        <option value="">Select MFS</option>
                                        <option value="Bkash">Bkash</option>
                                        <option value="Nagad">Nagad</option>
                                        <option value="Rocket">Rocket</option>
                                    </select>
                                </div>
                                <div class="col-md-4"><input type="text" name="online_trx_id" class="form-control" placeholder="Transaction ID"></div>
                                <div class="col-md-4"><input type="date" name="online_trx_dt" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="internet_banking_fields" class="dynamic-fields" style="display: none;">
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary">Internet Banking</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="bank_code" class="form-control">
                                        <option value="">Select Bank</option>
                                        @foreach($bankInfos as $bank)
                                        <option value="{{ $bank->bank_code }}">{{ $bank->bank_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><input type="text" name="bank_ac_no" class="form-control" placeholder="Account No"></div>
                                <div class="col-md-3"><input type="text" name="online_trx_id" class="form-control" placeholder="Transaction ID"></div>
                                <div class="col-md-3"><input type="date" name="online_trx_dt" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="giftcard_fields" class="dynamic-fields" style="display: none;">
                    <div class="alert alert-secondary">Gift Card/Points selected.</div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4 text-center">
            <button type="button" class="btn btn-lg btn-secondary px-4" id="resetBtn">
                <i class="fas fa-undo me-2"></i> Reset
            </button>
            <button type="submit" class="btn btn-lg btn-success px-4" id="submitBtn">
                <i class="fas fa-save me-2"></i> Process Payment
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
    $('#payments-sidebar, #payments-customer-payment-sidebar').addClass('active');
    $('#collapsePayments').addClass('show');

    // Initialize Select2 for customer
    $('#customer_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search customer by name or phone...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ url('admin/customers-search') }}",
            type: 'GET',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { search: params.term };
            },
            processResults: function(data) {
                return {
                    results: data.results || []
                };
            },
            cache: true
        },
        minimumInputLength: 1,
        templateResult: formatCustomer,
        templateSelection: formatCustomerSelection
    });

    function formatCustomer(customer) {
        if (customer.loading) return customer.text;
        if (!customer.name) return customer.text;
        return $('<div><strong>' + customer.name + '</strong><br><small>' + (customer.phone || '') + '</small></div>');
    }

    function formatCustomerSelection(customer) {
        if (!customer.name) return customer.text;
        return customer.name;
    }

    // Payment type change handler
    $('#payment_type').change(function() {
        var type = $(this).val();
        if (type == 'random') {
            $('#randomPaymentSection').show();
            $('#jobPaymentSection').hide();
        } else {
            $('#randomPaymentSection').hide();
            $('#jobPaymentSection').show();
            if ($('#customer_id').val()) {
                loadCustomerJobs();
            }
        }
    });

    // Customer change handler
    $('#customer_id').on('change', function() {
        if ($('#payment_type').val() == 'job' && $(this).val()) {
            loadCustomerJobs();
        }
    });

    function loadCustomerJobs() {
        var customerId = $('#customer_id').val();
        if (!customerId) return;

        $.ajax({
            url: "{{ url('admin/payments/get-customer-jobs') }}",
            type: 'GET',
            data: { customer_id: customerId },
            success: function(response) {
                if (response.success && response.jobs.length > 0) {
                    var html = '';
                    $.each(response.jobs, function(i, job) {
                        html += `<tr>
                            <td>${i+1}</td>
                            <td>${job.job_id}</td>
                            <td>${job.job_date}</td>
                            <td>${job.engine || '-'}</td>
                            <td class="text-end">${parseFloat(job.total_amount).toFixed(2)}</td>
                            <td class="text-end">${parseFloat(job.paid_amount).toFixed(2)}</td>
                            <td class="text-end due-amount" data-job="${job.id}" data-due="${job.due_amount}">${parseFloat(job.due_amount).toFixed(2)}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm pay-amount" data-job="${job.id}" data-due="${job.due_amount}" step="0.01" value="0" style="width: 100px;">
                             </td>
                        </tr>`;
                    });
                    $('#jobsTableBody').html(html);
                    calculateJobTotals();
                } else {
                    $('#jobsTableBody').html('<tr><td colspan="8" class="text-center text-muted">No unpaid jobs found</td></tr>');
                }
            }
        });
    }

    // Pay amount change handler
    $(document).on('input', '.pay-amount', function() {
        var due = parseFloat($(this).data('due'));
        var amount = parseFloat($(this).val()) || 0;
        if (amount > due) {
            $(this).val(due.toFixed(2));
        }
        calculateJobTotals();
    });

    function calculateJobTotals() {
    var totalTotal = 0;
    var totalPaid = 0;
    var totalDue = 0;
    var totalPayAmount = 0;

    // প্রতিটি জবের জন্য ক্যালকুলেশন
    $('.due-amount').each(function() {
        var due = parseFloat($(this).data('due')) || 0;
        var jobId = $(this).data('job');

        // এই জবের টোটাল অ্যামাউন্ট বের করো
        var total = 0;
        var paid = 0;

        // টোটাল এবং পেইড বের করার জন্য (টেবিল থেকে)
        var row = $(this).closest('tr');
        var totalText = row.find('td:eq(4)').text();
        var paidText = row.find('td:eq(5)').text();

        total = parseFloat(totalText) || 0;
        paid = parseFloat(paidText) || 0;

        totalTotal += total;
        totalPaid += paid;
        totalDue += due;
    });

    // পে অ্যামাউন্ট যোগ করো
    $('.pay-amount').each(function() {
        totalPayAmount += parseFloat($(this).val()) || 0;
    });

    $('#total_total').text(totalTotal.toFixed(2));
    $('#total_paid').text(totalPaid.toFixed(2));
    $('#total_due').text(totalDue.toFixed(2));
    $('#total_pay_amount').text(totalPayAmount.toFixed(2));
}

    // Payment mode change handler
    $('#payment_mode_id').change(function() {
        var modeName = $(this).find('option:selected').text();
        $('.dynamic-fields').hide();
        switch(modeName) {
            case 'Cash': $('#cash_fields').show(); break;
            case 'Cheque': $('#cheque_fields').show(); break;
            case 'Card': $('#card_fields').show(); break;
            case 'Mobile Banking': $('#mobile_banking_fields').show(); break;
            case 'Internet Banking': $('#internet_banking_fields').show(); break;
            case 'Gift Card': case 'Points': $('#giftcard_fields').show(); break;
            default: break;
        }
    });

    // Form submit
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();

        var customerId = $('#customer_id').val();
        var paymentType = $('#payment_type').val();
        var paymentModeId = $('#payment_mode_id').val();

        if (!customerId) {
            Swal.fire('Error', 'Please select a customer', 'error');
            return;
        }
        if (!paymentModeId) {
            Swal.fire('Error', 'Please select payment mode', 'error');
            return;
        }

        var formData = new FormData(this);
        formData.append('customer_id', customerId);
        formData.append('payment_type', paymentType);

        if (paymentType == 'random') {
            var amount = $('#random_amount').val();
            if (!amount || amount <= 0) {
                Swal.fire('Error', 'Please enter payment amount', 'error');
                return;
            }
            formData.append('payment_amount', amount);
        } else {
            var jobs = [];
            var hasPayment = false;
            $('.pay-amount').each(function() {
                var amount = parseFloat($(this).val()) || 0;
                if (amount > 0) {
                    hasPayment = true;
                    jobs.push({
                        job_id: $(this).data('job'),
                        amount: amount
                    });
                }
            });
            if (!hasPayment) {
                Swal.fire('Error', 'Please enter payment amount for at least one job', 'error');
                return;
            }
            formData.append('jobs', JSON.stringify(jobs));
        }

        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: "{{ url('admin/payments/store-customer-payment') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false })
                        .then(function() {
                            window.location.href = "{{ route('admin.payments.index') }}";
                        });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to process payment', 'error');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Failed to process payment';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error!', errorMsg, 'error');
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Process Payment');
            }
        });
    });
});
</script>
@endsection
