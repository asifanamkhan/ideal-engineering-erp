<script>
    // CSRF Token setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var PaymentModal = {
        config: {
            paymentFor: null, paymentForId: null, type: null, typeId: null,
            getDetailsUrl: null, processUrl: null, updateUrl: null,
            getHistoryUrl: null, getPaymentUrl: null, deleteUrl: null,
            modalTitle: 'Process Payment'
        },

        init: function(config) {
            this.config = $.extend({}, this.config, config);

            $('#paymentModalTitle').text(this.config.modalTitle);
            $('#payment_for').val(this.config.paymentFor);
            $('#payment_for_id').val(this.config.paymentForId);
            $('#type').val(this.config.type);
            $('#type_id').val(this.config.typeId);
            $('#edit_payment_id').val('');

            $('#submitBtn').html('<i class="fas fa-check-circle"></i> Process Payment');

            this.fetchPaymentDetails();
            this.fetchPaymentHistory();
            $('#paymentModal').modal('show');
        },

        fetchPaymentDetails: function() {
            $.ajax({
                url: this.config.getDetailsUrl,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#total_amount').text(response.total_amount);
                        $('#paid_amount').text(response.paid_amount);
                        $('#due_amount').text(response.due_amount);
                        $('#max_due').text(response.due_amount);
                        $('#due_amount').data('raw', response.raw_due);
                        $('#payment_amount').attr('max', response.raw_due);
                    }
                }
            });
        },

        fetchPaymentHistory: function() {
            $.ajax({
                url: this.config.getHistoryUrl + '/' + this.config.typeId,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        $('#payment_count').text(response.data.length);
                        var html = '';
                        $.each(response.data, function(i, p) {
                            html += '<tr><td>' + (i+1) + '</td>';
                            html += '<td>' + (p.payment_date || '-') + '</td>';
                            html += '<td>৳ ' + parseFloat(p.amount).toFixed(2) + '</td>';
                            html += '<td>' + (p.payment_mode || '-') + '</td>';
                            html += '<td>' + (p.narration || '-') + '</td>';
                            html += '<td><div class="dropdown"><button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-cog"></i></button>';
                            html += '<ul class="custom-dropdown dropdown-menu dropdown-menu-end">';
                            html += '<li><button class="custom-dropdown dropdown-item view-payment" data-id="' + p.id + '"><i class="fas fa-eye me-2 text-info"></i> View</button></li>';
                            html += '<li><button class="custom-dropdown dropdown-item edit-payment" data-id="' + p.id + '"><i class="fas fa-edit me-2 text-primary"></i> Edit</button></li>';
                            html += '<li><button class="custom-dropdown dropdown-item delete-payment" data-id="' + p.id + '"><i class="fas fa-trash me-2 text-danger"></i> Delete</button></li>';
                            html += '</ul></div></td></tr>';
                        });
                        $('#payment-history-body').html(html);
                    } else {
                        $('#payment-history-body').html('<tr><td colspan="6" class="text-center">No payment records found</td></tr>');
                        $('#payment_count').text('0');
                    }
                }
            });
        },

        showFieldsByMode: function(modeName) {
            $('.dynamic-fields').hide();
            switch(modeName) {
                case 'Cash': $('#cash_fields').show(); break;
                case 'Cheque': $('#cheque_fields').show(); break;
                case 'Card': $('#card_fields').show(); break;
                case 'Mobile Banking': $('#mobile_banking_fields').show(); break;
                case 'Internet Banking': $('#internet_banking_fields').show(); break;
                default: break;
            }
        },

        validateForm: function() {
            var paymentAmount = parseFloat($('#payment_amount').val());
            var dueAmount = $('#due_amount').data('raw');
            if (isNaN(paymentAmount) || paymentAmount <= 0) {
                Swal.fire({ icon: 'error', title: 'Invalid Amount', text: 'Please enter a valid payment amount.' });
                return false;
            }
            if (paymentAmount > dueAmount) {
                Swal.fire({ icon: 'error', title: 'Amount Exceeded', text: 'Payment amount cannot exceed due amount.' });
                return false;
            }
            if (!$('#payment_mode_id').val()) {
                Swal.fire({ icon: 'error', title: 'Required', text: 'Please select a payment mode.' });
                return false;
            }
            return true;
        },

        viewPayment: function(paymentId) {
            $.ajax({
                url: this.config.getPaymentUrl + '/' + paymentId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var p = response.data;
                        var html = '<table class="table table-bordered">';
                        html += '<tr><th width="40%">Date</th><td>' + (p.payment_date || '-') + '</td></tr>';
                        html += '<tr><th>Amount</th><td>' + (p.amount || '0') + '</td></tr>';
                        html += '<tr><th>Payment Mode</th><td>' + (p.payment_mode || '-') + '</td></tr>';
                        html += '<tr><th>Narration</th><td>' + (p.narration || '-') + '</td></tr>';
                        html += '<tr><th>Transaction ID</th><td>' + (p.online_trx_id || '-') + '</td></tr>';
                        html += '<tr><th>Cheque No</th><td>' + (p.chq_no || '-') + '</td></tr>';
                        html += '<tr><th>Card No</th><td>' + (p.card_no || '-') + '</td></tr>';
                        html += '<tr><th>MFS Name</th><td>' + (p.mfs_name || '-') + '</td></tr>';
                        html += '</table>';
                        $('#paymentDetailsBody').html(html);
                        $('#viewPaymentModal').modal('show');
                    }
                }
            });
        },

        editPayment: function(paymentId) {
            $.ajax({
                url: this.config.getPaymentUrl + '/' + paymentId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var p = response.data;
                        $('#payment-tab').trigger('click');
                        $('#edit_payment_id').val(p.id);
                        $('#payment_amount').val(p.amount);
                        $('#payment_mode_id').val(p.pay_method_id).trigger('change');
                        $('#narration').val(p.narration);
                        $('#submitBtn').html('<i class="fas fa-edit"></i> Update Payment');
                        if (p.chq_no) $('input[name="chq_no"]').val(p.chq_no);
                        if (p.chq_date) $('input[name="chq_date"]').val(p.chq_date);
                        if (p.card_no) $('input[name="card_no"]').val(p.card_no);
                        if (p.mfs_name) $('select[name="mfs_name"]').val(p.mfs_name);
                        if (p.bank_code) $('select[name="bank_code"]').val(p.bank_code);
                        if (p.bank_ac_no) $('input[name="bank_ac_no"]').val(p.bank_ac_no);
                        if (p.online_trx_id) $('input[name="online_trx_id"]').val(p.online_trx_id);
                        if (p.online_trx_dt) $('input[name="online_trx_dt"]').val(p.online_trx_dt);
                    }
                }
            });
        },

        deletePayment: function(paymentId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: this.config.deleteUrl,
                type: 'DELETE',
                data: {
                    payment_id: paymentId,
                    type_id: this.config.typeId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message });
                        PaymentModal.fetchPaymentDetails();
                        PaymentModal.fetchPaymentHistory();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Delete failed' });
                }
            });
        }
    });
},

        submitPayment: function(formData) {
            var isEdit = $('#edit_payment_id').val() ? true : false;
            var url = isEdit ? this.config.updateUrl : this.config.processUrl;

            Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: url, type: 'POST', data: formData, processData: false, contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        PaymentModal.fetchPaymentDetails();
                        PaymentModal.fetchPaymentHistory();
                        PaymentModal.resetForm();
                        Swal.fire({ icon: 'success', title: 'Success!', text: response.message });
                        $('#paymentModal').modal('hide');
                        $(document).trigger('paymentSuccess', [response]);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Something went wrong' });
                }
            });
        },

        resetForm: function() {
            $('#paymentForm')[0].reset();
            $('.dynamic-fields').hide();
            $('#payment_mode_id').val('');
            $('#payment_amount').val('');
            $('#edit_payment_id').val('');
            $('#submitBtn').html('<i class="fas fa-check-circle"></i> Process Payment');
        }
    };

    $(document).ready(function() {
        $(document).on('change', '#payment_mode_id', function() {
            PaymentModal.showFieldsByMode($(this).find('option:selected').text());
        });
        $(document).on('submit', '#paymentForm', function(e) {
            e.preventDefault();
            if (PaymentModal.validateForm()) PaymentModal.submitPayment(new FormData(this));
        });
        $(document).on('hidden.bs.modal', '#paymentModal', function() { PaymentModal.resetForm(); });
        $(document).on('click', '.view-payment', function() { PaymentModal.viewPayment($(this).data('id')); });
        $(document).on('click', '.edit-payment', function() { PaymentModal.editPayment($(this).data('id')); });
        $(document).on('click', '.delete-payment', function() { PaymentModal.deletePayment($(this).data('id')); });
    });
    window.PaymentHandler = PaymentModal;
</script>
