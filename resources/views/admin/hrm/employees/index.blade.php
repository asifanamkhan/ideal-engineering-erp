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
        .avatar-primary { background-color: #4e73df; }
        .avatar-success { background-color: #1cc88a; }
        .avatar-info { background-color: #36b9cc; }
        .avatar-warning { background-color: #f6c23e; }
        .avatar-danger { background-color: #e74a3b; }

        .badge {
            padding: 5px 10px;
            font-weight: 500;
        }
        .badge.bg-info { background-color: #36b9cc !important; color: white; }
        .badge.bg-success { background-color: #1cc88a !important; color: white; }
        .badge.bg-secondary { background-color: #858796 !important; color: white; }
        .badge.bg-warning { background-color: #f6c23e !important; color: #212529; }
        .badge.bg-primary { background-color: #4e73df !important; color: white; }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                <i class="fas fa-users me-2"></i>
                Employee list
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / employees</span>
            <div>
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-plus "></i> Add new employee
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm " id="employees-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="6%">Image</th>
                            <th width="10%">ID</th>
                            <th width="15%">Name</th>
                            <th width="12%">Designation</th>
                            <th width="10%">Branch</th>
                            <th width="12%">Phone</th>
                            <th width="10%">Join Date</th>
                            <th width="8%">Type</th>
                            <th width="6%">Status</th>
                            <th width="6%">Actions</th>
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
                Are you sure you want to delete this employee? This action cannot be undone and all their data will be
                lost.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="viewEmployeeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document"> <!-- Changed to modal-xl for larger size -->
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title text-white" id="viewEmployeeModalLabel">
                    <i class="fas fa-user-circle"></i> <span>Employee Details</span>
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="employeeDetails" style="max-height: 70vh; overflow-y: auto;">
                <!-- Details will be loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading employee details...</p>
                </div>
            </div>
            <div class="modal-footer">
                {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button> --}}
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
    $('#hrm-sidebar, #employee-index-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');

    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#employees-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.employees.index') }}",
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'employee_id',
                    name: 'employee_id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'designation',
                    name: 'designation', // Searchable by address fields
                    orderable: false
                },
                {
                    data: 'branch',
                    name: 'branch', // Searchable by address fields
                    orderable: false
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'join_date_formatted',
                    name: 'join_date'
                },
                {
                    data: 'employment_type_formatted',
                    name: 'employment_type'
                },
                {
                    data: 'status_badge',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[6, 'desc']], // Order by join date descending
            dom: '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search employees...",
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "No employees found",
                info: "Showing _START_ to _END_ of _TOTAL_ employees",
                infoEmpty: "No employees available",
                infoFiltered: "(filtered from _MAX_ total employees)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // View Employee
        $(document).on('click', '.view-btn', function() {
            var id = $(this).data('id');

            // Show loading
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we load employee details',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ url('admin/employees') }}/" + id,
                type: 'GET',
                success: function(response) {
                    Swal.close();

                    // Set modal title with employee name
                    // $('#viewEmployeeModal .modal-title').html('<i class="fas fa-user-circle me-2"></i>Employee Details');

                    // Load the HTML content
                    $('#employeeDetails').html(response.html);

                    // Show the modal
                    $('#viewEmployeeModal').modal('show');
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'Failed to load employee details.',
                        confirmButtonColor: '#28a745'
                    });
                }
            });
        });

        // Delete Employee
        var deleteId;
        $(document).on('click', '.delete-btn', function() {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').click(function() {
            $.ajax({
                url: "{{ url('admin/employees') }}/" + deleteId,
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
                        text: response.message || 'Employee has been deleted successfully.',
                        confirmButtonColor: '#28a745'
                    });
                },
                error: function(xhr) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'An error occurred while deleting the employee.',
                        confirmButtonColor: '#28a745'
                    });
                }
            });
        });
    });
</script>
@endsection
