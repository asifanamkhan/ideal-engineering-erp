@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
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
                    Customer list
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / customers</span>
            <div>
                <button type="button" class="btn btn-primary shadow-sm px-5" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> Add new customer
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="customers-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Customer ID</th>
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

<!-- Customer Modal (Create/Edit) -->
<!-- Customer Modal (Create/Edit) - Changed to modal-md -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <!-- Changed from modal-xl to modal-md -->
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title text-white" id="customerModalLabel">
                    <i class="fas fa-user-plus"></i> Add New Customer
                </h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-1x fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="customerModalBody">
                <!-- Form will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-circle"></i> Customer Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewCustomerBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading customer details...</p>
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
                Are you sure you want to delete this customer? This action cannot be undone.
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
     $('#contacts-sidebar, #customers-index-sidebar').addClass('active');
        $('#collapseContacts').addClass('show');

        $(document).ready(function() {
       

        let deleteId = null;
        let table = null;

        // Initialize DataTable
        function initDataTable() {
            table = $('#customers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.customers.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'customer_id', name: 'customer_id' },
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
                    searchPlaceholder: "Search customers...",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No customers found",
                    info: "Showing _START_ to _END_ of _TOTAL_ customers",
                    infoEmpty: "No customers available",
                    infoFiltered: "(filtered from _MAX_ total customers)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                },
                // Remove default Bootstrap 5 styling classes
                initComplete: function() {
                    // Remove form-control-sm class from search input
                    // $('.dataTables_filter input').removeClass('form-control-sm').addClass('form-control');
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
            $('#customerModalBody').html(`
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-pulse fa-3x text-primary"></i>
                    <p class="mt-3 text-muted">Loading form...</p>
                </div>
            `);
            
            // Load the form HTML
            $.ajax({
                url: "{{ route('admin.customers.form') }}",
                type: 'GET',
                success: function(response) {
                    $('#customerModalBody').html(response);
                    
                    if (mode === 'edit' && id) {
                        // Show loading while fetching customer data
                        $('#customerModalBody').html(`
                            <div class="text-center py-5">
                                <i class="fas fa-spinner fa-pulse fa-3x text-primary"></i>
                                <p class="mt-3 text-muted">Loading customer data...</p>
                            </div>
                        `);
                        
                        // Load customer data for edit
                        $.ajax({
                            url: "{{ url('admin/customers') }}/" + id + "/edit",
                            type: 'GET',
                            success: function(res) {
                                if (res.customer) {
                                    // Reload the form first
                                    $.ajax({
                                        url: "{{ route('admin.customers.form') }}",
                                        type: 'GET',
                                        success: function(formHtml) {
                                            $('#customerModalBody').html(formHtml);
                                            
                                            // Fill form data
                                            $('#customer_id').val(res.customer.id);
                                            $('#name').val(res.customer.name);
                                            $('#email').val(res.customer.email);
                                            $('#phone').val(res.customer.phone);
                                            $('#type').val(res.customer.type).trigger('change');
                                            $('#address').val(res.customer.address);
                                            $('#reference').val(res.customer.reference);
                                            $('#opening_bal').val(res.customer.opening_bal);
                                            $('#business_name').val(res.customer.business_name);
                                            $('#business_phone').val(res.customer.business_phone);
                                            $('#business_address').val(res.customer.business_address);
                                            $('#tax_no').val(res.customer.tax_no);
                                            $('#status').val(res.customer.status);
                                            
                                            $('#customerModalLabel').html('<i class="fas fa-edit"></i> Edit Customer');
                                        },
                                        error: function() {
                                            $('#customerModalBody').html(`
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
                                $('#customerModalBody').html(`
                                    <div class="text-center py-5">
                                        <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                                        <p class="mt-3 text-danger">Failed to load customer data</p>
                                    </div>
                                `);
                            }
                        });
                    } else {
                        $('#customerModalLabel').html('<i class="fas fa-user-plus"></i> Add New Customer');
                        if ($('#customerForm')[0]) {
                            $('#customerForm')[0].reset();
                        }
                        $('#customer_id').val('');
                    }
                },
                error: function() {
                    $('#customerModalBody').html(`
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                            <p class="mt-3 text-danger">Failed to load form</p>
                        </div>
                    `);
                }
            });
        }

        // Add Customer Button
        $('#addCustomerBtn').click(function() {
            loadForm('create');
            $('#customerModal').modal('show');
        });

        // Edit Customer
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            loadForm('edit', id);
            $('#customerModal').modal('show');
        });

        // Save Customer
        $('#saveCustomerBtn').click(function() {
            let $btn = $(this);
            let originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            
            let id = $('#customer_id').val();
            let url = id ? "{{ url('admin/customers') }}/" + id : "{{ route('admin.customers.store') }}";
            let method = id ? 'PUT' : 'POST';
            
            let formData = $('#customerForm').serialize();
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#customerModal').modal('hide');
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

        // View Customer
        // $(document).on('click', '.view-btn', function() {
        //     let id = $(this).data('id');
            
        //     $('#viewCustomerBody').html(`
        //         <div class="text-center py-5">
        //             <i class="fas fa-circle-notch fa-spin fa-3x text-info"></i>
        //             <p class="mt-3 text-muted">Loading customer details...</p>
        //         </div>
        //     `);
            
        //     $.ajax({
        //         url: "{{ url('admin/customers') }}/" + id,
        //         type: 'GET',
        //         success: function(response) {
        //             $('#viewCustomerBody').html(response.html);
        //             $('#viewCustomerModal').modal('show');
        //         },
        //         error: function(xhr) {
        //             $('#viewCustomerBody').html(`
        //                 <div class="text-center py-5">
        //                     <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
        //                     <p class="mt-3 text-danger">Failed to load customer details</p>
        //                     <button class="btn btn-sm btn-primary mt-3" onclick="location.reload()">Refresh</button>
        //                 </div>
        //             `);
        //             Swal.fire({
        //                 icon: 'error',
        //                 title: 'Error!',
        //                 text: 'Failed to load customer details.',
        //                 confirmButtonColor: '#28a745'
        //             });
        //         }
        //     });
        // });

        // Delete Customer
        $(document).on('click', '.delete-btn', function() {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDeleteBtn').click(function() {
            let $btn = $(this);
            let originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
            
            $.ajax({
                url: "{{ url('admin/customers') }}/" + deleteId,
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
                        text: response.message || 'Customer deleted successfully!',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        showConfirmButton: true
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'Failed to delete customer.',
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
        $('#customerModal').on('hidden.bs.modal', function() {
            $('#customerForm')[0]?.reset();
            $('#customer_id').val('');
            $('.business-fields').hide();
            $('#type').val('individual').trigger('change');
        });

        // Initialize DataTable
        initDataTable();
    });
</script>
@endsection