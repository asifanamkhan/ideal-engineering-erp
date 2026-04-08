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

    .badge.bg-info {
        background-color: #36b9cc !important;
        color: white;
    }

    .badge.bg-success {
        background-color: #1cc88a !important;
        color: white;
    }

    .badge.bg-secondary {
        background-color: #858796 !important;
        color: white;
    }

    .badge.bg-warning {
        background-color: #f6c23e !important;
        color: #212529;
    }

    .badge.bg-primary {
        background-color: #4e73df !important;
        color: white;
    }

    .badge.bg-danger {
        background-color: #e74a3b !important;
        color: white;
    }

    .gap-1 {
        gap: 0.25rem;
    }

    .customer-info,
    .date-info {
        line-height: 1.5;
    }

    .table td {
        vertical-align: middle;
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
                            <th width="3%">#</th>
                            <th width="8%">Job ID</th>
                            <th width="20%">Customer</th>
                            <th width="14%">Date</th>
                            <th width="8%">Engine</th>
                            <th width="8%">Status</th>
                            <th width="8%">Parts</th>
                            <th width="11%">Quotation</th>
                            <th width="11%">Invoice</th>
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

<!-- View Job Modal -->
<div class="modal fade" id="viewJobModal" tabindex="-1" role="dialog" aria-labelledby="viewJobModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="viewJobModalLabel">
                    <i class="fas fa-book-open"></i> Job Details
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="jobDetails" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading job details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
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
                data: 'parts',
                name: 'parts',
                orderable: false,
                searchable: false
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
</script>
@endsection
