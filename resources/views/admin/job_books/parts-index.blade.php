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
                    <i class="fas fa-microchip me-2"></i>
                    Job Purse List
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / Jobs / Purse</span>
            <div>
                <a href="{{ route('admin.job-parts.create') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-plus"></i> Add Purse for Job
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="parts-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Job ID</th>
                            <th width="20%">Customer Info</th>
                            <th width="15%">Vehicle</th>
                            <th width="10%">Job Date</th>
                            <th width="10%">Purse Amount</th>
                            <th width="10%">Purse Status</th>
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

<!-- View Purse Modal -->
<div class="modal fade" id="viewPartsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Purse Details</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="partsDetailsBody">
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
                Are you sure you want to delete these parts? This action cannot be undone.
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
    $('#parts-index-sidebar, #jobs-sidebar').addClass('active');
    $('#collapseJobs').addClass('show');

    var table = $('#parts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.job-parts.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'job_id', name: 'job_books.job_id', orderable: true, searchable: true },
            { data: 'customer_info', name: 'customer_name', orderable: true, searchable: true },
            { data: 'engine', name: 'engine', orderable: true, searchable: true },
            { data: 'job_date', name: 'job_books.job_date', orderable: true, searchable: true },
            { data: 'parts_amount', name: 'parts_amount', orderable: true, searchable: false, className: 'text-end' },
            { data: 'parts_status_badge', name: 'parts_status', orderable: true, searchable: true, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search jobs...",
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

    // View Purse
    $(document).on('click', '.view-parts', function() {
        var id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/job-parts/get-details') }}/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var html = '<div class="table-responsive">';
                    html += '<table class="table table-bordered">';
                    html += '<tr><th width="30%">Job ID</th><td>' + (response.job.job_id || '-') + '</td></tr>';
                    html += '<tr><th>Customer</th><td>' + (response.job.customer_name || '-') + '</td></tr>';
                    html += '<tr><th>Customer Phone</th><td>' + (response.job.customer_phone || '-') + '</td></tr>';
                    html += '<tr><th>Purse Status</th><td>';

                    var status = response.job.parts_status || 'not_received';
                    if (status == 'received') html += '<span class="badge bg-success">Received</span>';
                    else if (status == 'partial') html += '<span class="badge bg-warning">Partial</span>';
                    else html += '<span class="badge bg-danger">Not Received</span>';
                    html += '</td></tr>';
                    html += '<tr><th>Purse Amount</th><td>৳ ' + parseFloat(response.job.parts_amount || 0).toFixed(2) + '</td></tr>';
                    html += '<tr><th>Purse Description</th><td>' + (response.job.parts_description || '-') + '</td></tr>';
                    html += '</table>';

                    if (response.parts && response.parts.length > 0) {
                        html += '<h6 class="mt-3">Purse List:</h6>';
                        html += '<table class="table table-sm table-bordered">';
                        html += '<thead><tr><th>#</th><th>Part Name</th><th>Brand/Model</th><th>Size</th><th>Qty</th></tr></thead><tbody>';
                        $.each(response.parts, function(i, part) {
                            html += '<tr>';
                            html += '<td>' + (i+1) + '</td>';
                            html += '<td>' + (part.part_name || '-') + '</td>';
                            html += '<td>' + (part.brand || '') + ' ' + (part.model || '') + '</td>';
                            html += '<td>' + (part.size_name || '-') + '</td>';
                            html += '<td class="text-center">' + (part.quantity || 0) + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table>';
                    } else {
                        html += '<div class="alert alert-info mt-3">No parts found</div>';
                    }
                    html += '</div>';

                    $('#partsDetailsBody').html(html);
                    $('#viewPartsModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message || 'Failed to load parts details', 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to load parts details', 'error');
            }
        });
    });

    // Delete Purse
    var deleteId;
    $(document).on('click', '.delete-parts', function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: "{{ url('admin/job-parts/delete') }}/" + deleteId,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message });
            },
            error: function() {
                $('#deleteModal').modal('hide');
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete parts' });
            }
        });
    });

    // Print Purse
$(document).on('click', '.print-parts', function() {
    var jobId = $(this).data('id');

    Swal.fire({
        title: 'Preparing parts list...',
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
            documents: ['parts']
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
                    text: response.message || 'Failed to generate parts list.'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'An error occurred while preparing parts list.'
            });
        }
    });
});
</script>
@endsection
