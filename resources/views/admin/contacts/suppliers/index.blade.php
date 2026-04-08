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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-users me-2"></i>
                    Supplier list
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / suppliers</span>
            <div>
                <button type="button" class="btn btn-primary shadow-sm px-5" id="addSupplierBtn">
                    <i class="fas fa-plus"></i> Add new supplier
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="suppliers-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Supplier ID</th>
                            <th width="15%">Name</th>
                            <th width="12%">Phone</th>
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

<!-- Supplier Modal (Create/Edit) -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title text-white" id="supplierModalLabel">
                    <i class="fas fa-user-plus"></i> Add New Supplier
                </h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-1x fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="supplierModalBody">
                <!-- Form will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveSupplierBtn">Save Supplier</button>
            </div>
        </div>
    </div>
</div>

<!-- View Supplier Modal -->
<div class="modal fade" id="viewSupplierModal" tabindex="-1" aria-labelledby="viewSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-circle"></i> Supplier Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSupplierBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading supplier details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this supplier? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
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
    $('#contacts-sidebar, #suppliers-index-sidebar').addClass('active');
    $('#collapseContacts').addClass('show');

    $(document).ready(function() {
        let deleteId = null;
        let table = null;

        // Initialize DataTable
        function initDataTable() {
            table = $('#suppliers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.suppliers.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'supplier_id', name: 'supplier_id' },
                    { data: 'name', name: 'name' },
                    { data: 'phone', name: 'phone' },
                    { data: 'type', name: 'type' },
                    { data: 'status_badge', name: 'status_badge' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 10,
                responsive: true,
                order: [[1, 'asc']],
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search suppliers...",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No suppliers found",
                    info: "Showing _START_ to _END_ of _TOTAL_ suppliers",
                    infoEmpty: "No suppliers available",
                    infoFiltered: "(filtered from _MAX_ total suppliers)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load data table. Please refresh the page.',
                        confirmButtonColor: '#28a745'
                    });
                }
            });
        }

        // Load form in modal
        function loadForm(mode, id = null) {
            $('#supplierModalBody').html(`
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-pulse fa-3x text-primary"></i>
                    <p class="mt-3 text-muted">Loading form...</p>
                </div>
            `);
            
            // Load the form HTML
            $.ajax({
                url: "{{ route('admin.suppliers.form') }}",
                type: 'GET',
                success: function(response) {
                    $('#supplierModalBody').html(response);
                    
                    if (mode === 'edit' && id) {
                        // Show loading while fetching supplier data
                        $('#supplierModalBody').html(`
                            <div class="text-center py-5">
                                <i class="fas fa-spinner fa-pulse fa-3x text-primary"></i>
                                <p class="mt-3 text-muted">Loading supplier data...</p>
                            </div>
                        `);
                        
                        // Load supplier data for edit
                        $.ajax({
                            url: "{{ url('admin/suppliers') }}/" + id + "/edit",
                            type: 'GET',
                            success: function(res) {
                                if (res.supplier) {
                                    // Reload the form first
                                    $.ajax({
                                        url: "{{ route('admin.suppliers.form') }}",
                                        type: 'GET',
                                        success: function(formHtml) {
                                            $('#supplierModalBody').html(formHtml);
                                            
                                            // Fill form data
                                            $('#supplier_id').val(res.supplier.id);
                                            $('#name').val(res.supplier.name);
                                            $('#email').val(res.supplier.email);
                                            $('#phone').val(res.supplier.phone);
                                            $('#type').val(res.supplier.type).trigger('change');
                                            $('#address').val(res.supplier.address);
                                            $('#reference').val(res.supplier.reference);
                                            $('#opening_bal').val(res.supplier.opening_bal);
                                            $('#business_name').val(res.supplier.business_name);
                                            $('#business_phone').val(res.supplier.business_phone);
                                            $('#business_address').val(res.supplier.business_address);
                                            $('#tax_no').val(res.supplier.tax_no);
                                            $('#status').val(res.supplier.status);
                                            
                                            $('#supplierModalLabel').html('<i class="fas fa-edit"></i> Edit Supplier');
                                        },
                                        error: function() {
                                            $('#supplierModalBody').html(`
                                                <div class="text-center py-5">
                                                    <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                                                    <p class="mt-3 text-danger">Failed to load form</p>
                                                </div>
                                            `);
                                        }
                                    });
                                }
                            },
                            error: function() {
                                $('#supplierModalBody').html(`
                                    <div class="text-center py-5">
                                        <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                                        <p class="mt-3 text-danger">Failed to load supplier data</p>
                                    </div>
                                `);
                            }
                        });
                    } else {
                        $('#supplierModalLabel').html('<i class="fas fa-user-plus"></i> Add New Supplier');
                        if ($('#supplierForm')[0]) {
                            $('#supplierForm')[0].reset();
                        }
                        $('#supplier_id').val('');
                    }
                },
                error: function() {
                    $('#supplierModalBody').html(`
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                            <p class="mt-3 text-danger">Failed to load form</p>
                        </div>
                    `);
                }
            });
        }

        // Add Supplier Button
        $('#addSupplierBtn').click(function() {
            loadForm('create');
            $('#supplierModal').modal('show');
        });

        // Edit Supplier
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            loadForm('edit', id);
            $('#supplierModal').modal('show');
        });

        // Save Supplier
        $('#saveSupplierBtn').click(function() {
            let $btn = $(this);
            let originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            
            let id = $('#supplier_id').val();
            let url = id ? "{{ url('admin/suppliers') }}/" + id : "{{ route('admin.suppliers.store') }}";
            let method = id ? 'PUT' : 'POST';
            
            let formData = $('#supplierForm').serialize();
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#supplierModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#28a745',
                            timer: 2000,
                            showConfirmButton: true
                        });
                        table.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += `<strong>${key}:</strong> ${value[0]}<br>`;
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error!',
                            html: errorMessage,
                            confirmButtonColor: '#28a745'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Something went wrong!',
                            confirmButtonColor: '#28a745'
                        });
                    }
                },
                complete: function() {
                    $btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // View Supplier
        $(document).on('click', '.view-btn', function() {
            let id = $(this).data('id');
            
            $('#viewSupplierBody').html(`
                <div class="text-center py-5">
                    <i class="fas fa-circle-notch fa-spin fa-3x text-info"></i>
                    <p class="mt-3 text-muted">Loading supplier details...</p>
                </div>
            `);
            
            $.ajax({
                url: "{{ url('admin/suppliers') }}/" + id,
                type: 'GET',
                success: function(response) {
                    $('#viewSupplierBody').html(response.html);
                    $('#viewSupplierModal').modal('show');
                },
                error: function(xhr) {
                    $('#viewSupplierBody').html(`
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                            <p class="mt-3 text-danger">Failed to load supplier details</p>
                            <button class="btn btn-sm btn-primary mt-3" onclick="location.reload()">Refresh</button>
                        </div>
                    `);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load supplier details.',
                        confirmButtonColor: '#28a745'
                    });
                }
            });
        });

        // Delete Supplier
        $(document).on('click', '.delete-btn', function() {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDeleteBtn').click(function() {
            let $btn = $(this);
            let originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
            
            $.ajax({
                url: "{{ url('admin/suppliers') }}/" + deleteId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message || 'Supplier deleted successfully!',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        showConfirmButton: true
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'Failed to delete supplier.',
                        confirmButtonColor: '#28a745'
                    });
                },
                complete: function() {
                    $btn.html(originalText).prop('disabled', false);
                    $('#deleteModal').modal('hide');
                }
            });
        });

        // Reset form when modal is closed
        $('#supplierModal').on('hidden.bs.modal', function() {
            $('#supplierForm')[0]?.reset();
            $('#supplier_id').val('');
            $('.business-fields').hide();
            $('#type').val('individual').trigger('change');
        });

        // Initialize DataTable
        initDataTable();
    });
</script>
@endsection