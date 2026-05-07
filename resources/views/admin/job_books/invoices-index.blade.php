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
                    <i class="fas fa-receipt me-2"></i>
                    Invoices List
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / Jobs / Invoices</span>
            <div>
                <a href="{{ route('admin.job-invoices.create') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-plus"></i> Add New Invoice
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="invoices-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Job ID</th>
                            <th width="20%">Customer Info</th>
                            <th width="15%">Vehicle</th>
                            <th width="10%"> Date</th>
                            <th width="10%"> Amount</th>
                            <th width="10%">Status</th>
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

<!-- View Invoice Modal -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Invoice Details</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceDetailsBody">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                Are you sure you want to delete this invoice? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#invoices-index-sidebar, #jobs-sidebar').addClass('active');
    $('#collapseJobs').addClass('show');

    var table = $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.job-invoices.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'job_id', name: 'job_books.job_id', orderable: true, searchable: true },
            { data: 'customer_info', name: 'customer_name', orderable: true, searchable: true },
            { data: 'engine', name: 'engine', orderable: true, searchable: true },
            { data: 'invoice_date', name: 'invoice_date', orderable: true, searchable: true },
            { data: 'invoice_amount', name: 'invoice_amount', orderable: true, searchable: false, className: 'text-end' },
            { data: 'invoice_status_badge', name: 'invoice_status', orderable: true, searchable: true, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search invoices...",
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No records found",
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            infoEmpty: "No records available",
            infoFiltered: "(filtered from _MAX_ total records)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        }
    });

    // View Invoice
    // View Invoice
$(document).on('click', '.view-invoice', function() {
    var id = $(this).data('id');

    $.ajax({
        url: "{{ url('admin/job-invoices/get-details') }}/" + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var html = '<div class="table-responsive">';
                html += '<table class="table table-bordered">';
                html += '<tr><th width="30%">Job ID</th><td>' + (response.job.job_id || '-') + '<\/td><\/tr>';
                html += '<tr><th>Invoice Date</th><td>' + (response.job.invoice_date || '-') + '<\/td><\/tr>';
                html += '<tr><th>Invoice Status</th><td>';

                var status = response.job.invoice_status || 'unpaid';
                if (status == 'paid') html += '<span class="badge bg-success">Paid</span>';
                else if (status == 'partial') html += '<span class="badge bg-warning">Partial</span>';
                else html += '<span class="badge bg-danger">Unpaid</span>';
                html += '<\/td><\/tr>';
                html += '<tr><th>Invoice Amount</th><td>৳ ' + parseFloat(response.job.invoice_amount || 0).toFixed(2) + '<\/td><\/tr>';
                html += '<tr><th>Discount</th><td>৳ ' + parseFloat(response.job.invoice_discount || 0).toFixed(2) + '<\/td><\/tr>';
                html += '<tr><th>Description</th><td>' + (response.job.invoice_description || '-') + '<\/td><\/tr>';
                html += '</table>';

                if (response.services && response.services.length > 0) {
                    html += '<h6 class="mt-3">Services List:</h6>';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>#</th><th>Service Name</th><th>Unit</th><th>Qty</th><th>Unit Price</th><th>Total</th><\/thead><tbody>';
                    $.each(response.services, function(i, service) {
                        html += '<tr>';
                        html += '<td>' + (i+1) + '<\/td>';
                        html += '<td>' + (service.service_name || '-') + '<\/td>';
                        html += '<td>' + (service.unit_name || '-') + '<\/td>';
                        html += '<td class="text-center">' + (service.quantity || 0) + '<\/td>';
                        html += '<td class="text-end">৳ ' + parseFloat(service.price || 0).toFixed(2) + '<\/td>';
                        html += '<td class="text-end">৳ ' + parseFloat(service.total_price || 0).toFixed(2) + '<\/td>';
                        html += '<\/tr>';
                    });
                    html += '<\/tbody><\/table>';
                } else {
                    html += '<div class="alert alert-info mt-3">No services found<\/div>';
                }
                html += '<\/div>';

                $('#invoiceDetailsBody').html(html);
                $('#viewInvoiceModal').modal('show');
            } else {
                Swal.fire('Error!', response.message || 'Failed to load invoice details', 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Failed to load invoice details', 'error');
        }
    });
});

    // Print Invoice
    $(document).on('click', '.print-invoice', function() {
        var jobId = $(this).data('id');

        Swal.fire({
            title: 'Preparing invoice...',
            text: 'Please wait while we prepare your document.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "{{ url('admin/job-books/print') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                job_id: jobId,
                documents: ['invoice']
            },
            success: function(response) {
                Swal.close();
                if (response.success && response.html) {
                    var printTab = window.open();
                    printTab.document.write(response.html);
                    printTab.document.close();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to generate invoice.'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred while preparing invoice.'
                });
            }
        });
    });

    // Delete Invoice
    var deleteId;
    $(document).on('click', '.delete-invoice', function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: "{{ url('admin/job-invoices/delete') }}/" + deleteId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message });
            },
            error: function() {
                $('#deleteModal').modal('hide');
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete invoice' });
            }
        });
    });
</script>
@endsection
