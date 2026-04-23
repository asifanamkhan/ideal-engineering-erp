@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        text-transform: uppercase;
    }

    .avatar-primary {
        background-color: #4e73df;
    }

    .avatar-success {
        background-color: #1cc88a;
    }

    .avatar-info {
        background-color: #36b9cc;
    }

    .avatar-warning {
        background-color: #f6c23e;
    }

    .avatar-danger {
        background-color: #e74a3b;
    }
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .customer-info,
    .date-info {
        line-height: 1.5;
    }

    .table td {
        vertical-align: middle;
    }

    .print-option {
        width: 18px;
        height: 18px;
    }

    .dynamic-fields {
    margin-top: 15px;
}

    #paymentModal .card {
        margin-bottom: 0;
    }

    #paymentModal .modal-header.bg-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    }

    #payment_amount {
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
                <h4 class="mb-1">
                    <i class="fas fa-book me-2"></i>
                    Job List
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / Jobs</span>
            <div>
                <a href="{{ route('admin.job-books.create') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-plus"></i> Add new job
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="jobs-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="4%">#</th>
                            <th width="8%">Job ID</th>
                            <th width="18%">Customer</th>
                            <th width="15%">Date</th>
                            <th width="8%">Engine</th>
                            <th width="14%">Status</th>
                            <th width="14%">Quotation</th>
                            <th width="14%">Invoice</th>
                            <th width="5%">Actions</th>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this job? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Options Modal -->
<div class="modal fade" id="printOptionsModal" tabindex="-1" role="dialog" aria-labelledby="printOptionsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printOptionsModalLabel">
                    <i class="fas fa-print me-2"></i> Print Options
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="fw-bold mb-3">Select documents to print:</p>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input print-option" value="parts"
                        id="printParts" style="width: 22px; height: 22px; cursor: pointer; margin-top: 0;">
                    <label class="form-check-label fw-bold" for="printParts" style="cursor: pointer; font-size: 15px; margin-left: 12px;">
                        Parts List
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input print-option" value="quotation"
                        id="printQuotation" style="width: 22px; height: 22px; cursor: pointer; margin-top: 0;">
                    <label class="form-check-label fw-bold" for="printQuotation" style="cursor: pointer; font-size: 15px; margin-left: 12px;">
                        Quotation
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input print-option" value="invoice"
                        id="printInvoice" style="width: 22px; height: 22px; cursor: pointer; margin-top: 0;">
                    <label class="form-check-label fw-bold" for="printInvoice" style="cursor: pointer; font-size: 15px; margin-left: 12px;">
                        Invoice
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmPrint">Print</button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.payments.payment-section', ['inline' => false])

@endsection

@section('js')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Activate sidebar
    $('#jobs-sidebar, #jobs-index-sidebar').addClass('active');
    $('#collapseJobs').addClass('show');

    $(document).ready(function() {
    // Initialize DataTable
    var table = $('#jobs-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.job-books.index') }}",
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'job_id',
                name: 'job_id',
                orderable: true,
                searchable: true
            },
            {
                data: 'customer',
                name: 'customer',
                orderable: true,
                searchable: true
            },
            {
                data: 'date',
                name: 'date',
                orderable: true,
                searchable: true
            },
            {
                data: 'engine',
                name: 'engine',
                orderable: true,
                searchable: true,
                className: 'text-center'
            },
            {
                data: 'status_badge',
                name: 'status',
                orderable: true,
                searchable: true,
                className: 'text-center'
            },

            {
                data: 'quotation',
                name: 'quotation',
                orderable: false,
                searchable: false
            },
            {
                data: 'invoice',
                name: 'invoice',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[1, 'desc']], // Order by job ID descending
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search jobs...",
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No jobs found",
            info: "Showing _START_ to _END_ of _TOTAL_ jobs",
            infoEmpty: "No jobs available",
            infoFiltered: "(filtered from _MAX_ total jobs)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        }
    });

    // Handle Create/View Quotation
    $(document).on('click', '.create-quotation, .view-quotation', function() {
        var jobId = $(this).data('id');
        window.location.href = "{{ url('admin/job-quotations/create') }}/" + jobId;
    });

    // View Job Details
    $(document).on('click', '.view-btn', function() {
        var jobId = $(this).data('id');
        $('#viewJobModal').modal('show');

        $.ajax({
            url: "{{ url('admin/job-books') }}/" + jobId,
            type: 'GET',
            success: function(response) {
                $('#jobDetails').html(response);
            },
            error: function(xhr) {
                $('#jobDetails').html('<div class="alert alert-danger">Error loading job details.</div>');
            }
        });
    });

    // Delete Job
    var deleteId;
    $(document).on('click', '.delete-btn', function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: "{{ url('admin/job-books') }}/" + deleteId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: response.message || 'Job has been deleted successfully.',
                    confirmButtonColor: '#28a745'
                });
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'An error occurred while deleting the job.',
                    confirmButtonColor: '#28a745'
                });
            }
        });
    });
});

// Print functionality
var printJobId = null;

$(document).on('click', '.print-btn', function() {
    printJobId = $(this).data('id');
    // Reset checkboxes
    $('.print-option').prop('checked', false);
    $('#printOptionsModal').modal('show');
});

$('#confirmPrint').click(function() {
    var selectedOptions = [];

    // Get selected options
    $('.print-option:checked').each(function() {
        selectedOptions.push($(this).val());
    });

    if (selectedOptions.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one document to print.',
            confirmButtonColor: '#28a745'
        });
        return;
    }

    // Close the modal
    $('#printOptionsModal').modal('hide');

    // Show loading
    Swal.fire({
        title: 'Preparing documents...',
        html: 'Please wait while we prepare your documents.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Make AJAX request to get print data
    $.ajax({
        url: "{{ url('admin/job-books/print') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            job_id: printJobId,
            documents: selectedOptions
        },
        success: function(response) {
            Swal.close();

            if (response.success && response.html) {
                // Open print preview in a new tab instead of window
                var printTab = window.open();
                printTab.document.write(response.html);
                printTab.document.close();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message || 'Failed to generate print documents.',
                    confirmButtonColor: '#28a745'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'An error occurred while preparing print documents.',
                confirmButtonColor: '#28a745'
            });
        }
    });
});

</script>

@include('admin.partials.payments.payment-script')
<script>
    $(document).on('click', '.payment-btn', function() {
        var jobId = $(this).data('id');
        var customerId = $(this).data('customer-id');

        PaymentHandler.init({
            paymentFor: 'customer',
            paymentForId: customerId,
            type: 'job',
            typeId: jobId,
            getDetailsUrl: "{{ url('admin/job-books/get-payment-details') }}/" + jobId,
            processUrl: "{{ url('admin/job-books/process-payment') }}",
            updateUrl: "{{ url('admin/job-books/update-payment') }}",
            getHistoryUrl: "{{ url('admin/job-books/get-payment-history') }}",
            getPaymentUrl: "{{ url('admin/job-books/get-payment') }}",
            deleteUrl: "{{ url('admin/job-books/delete-payment') }}",
            modalTitle: 'Job Payment',
            inline: false
        });
    });
</script>
@endsection
