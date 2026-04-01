{{-- resources/views/admin/settings/users/index.blade.php --}}
@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    .employee-info-card {
        background-color: #f8f9fa;
        border-left: 4px solid #4e73df;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }
    .employee-info-card h6 {
        color: #4e73df;
        margin-bottom: 10px;
    }
    .employee-info-card p {
        margin-bottom: 5px;
        font-size: 14px;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple {
        min-height: 38px;
    }
    .role-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-users"></i> All Users List</h4>
            </div>
            <div>
                <a href="#" class="btn btn-primary shadow-sm btn px-5" id="addNewUser">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="users-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role(s)</th>
                            <th>Status</th>
                            <th>Actions</th>
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

<!-- Create/Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add New User</h5>

            </div>
            <form id="userForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="user_id" name="user_id">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="employee_id" class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Select an employee</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Employee Info Display -->
                    <div id="employeeInfo" class="employee-info-card" style="display: none;">
                        <h6><i class="fas fa-user-circle"></i> Employee Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <span id="emp_name"></span></p>
                                <p><strong>Email:</strong> <span id="emp_email"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Designation:</strong> <span id="emp_designation"></span></p>
                                <p><strong>Phone:</strong> <span id="emp_phone"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="roles" class="form-label">Assign Roles <span class="text-danger">*</span></label>
                            <select class="form-select" id="roles" name="roles[]" multiple="multiple" required>
                                <option value="">Select roles</option>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">You can select multiple roles for this user</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Minimum 8 characters. Leave blank to keep current password (for edit).</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $('#settings-sidebar').addClass('active');
    $('#users-index-sidebar').addClass('active');
    $('#collapseSettings').addClass('show');

    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.users.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'role_name', name: 'role_name' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '15%', className: 'text-center' }
            ],
            order: [[1, 'asc']],
            dom: '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search users...",
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "No users found",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                infoEmpty: "No users available",
                infoFiltered: "(filtered from _MAX_ total users)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Initialize Select2 for employee search with auto focus
        $('#employee_id').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#userModal'),
            placeholder: 'Search and select an employee',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('admin.employees.search') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection
        });

        // Auto focus on search input when dropdown opens
        $('#employee_id').on('select2:open', function(e) {
            setTimeout(function() {
                // Find the search input in the open dropdown
                var $searchField = $('.select2-container--open .select2-search__field');
                if ($searchField.length) {
                    $searchField[0].focus();
                }
            }, 100);
        });

        // Initialize Select2 for roles (multiple)
        $('#roles').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#userModal'),
            placeholder: 'Select roles',
            allowClear: true,
            width: '100%'
        });

        function formatEmployee(employee) {
            if (employee.loading) return employee.text;

            var $container = $(
                `<div class="d-flex align-items-center">
                    <div>
                        <strong>${employee.name}</strong><br>
                        <small class="">${employee.email} | ${employee.designation}</small>
                    </div>
                </div>`
            );
            return $container;
        }

        function formatEmployeeSelection(employee) {
            return employee.name || employee.text;
        }

        // Load employees and roles when modal opens
        function loadEmployeesAndRoles() {
            $.ajax({
                url: "{{ route('admin.users.create') }}",
                type: 'GET',
                success: function(data) {
                    // Clear and populate employees select
                    $('#employee_id').empty().append('<option value="">Select an employee</option>');
                    $.each(data.employees, function(key, employee) {
                        $('#employee_id').append(`<option value="${employee.id}">${employee.name} (${employee.email})</option>`);
                    });
                    $('#employee_id').trigger('change');

                    // Populate roles select (multiple)
                    $('#roles').empty();
                    $.each(data.roles, function(key, role) {
                        $('#roles').append(`<option value="${role.id}">${role.name}</option>`);
                    });
                    $('#roles').trigger('change');
                }
            });
        }

        // Get employee details when selected
        $('#employee_id').on('change', function() {
            var employeeId = $(this).val();
            if (employeeId) {
                $.ajax({
                    url: "{{ url('admin/users/employee') }}/" + employeeId,
                    type: 'GET',
                    success: function(data) {
                        $('#emp_name').text(data.name);
                        $('#emp_email').text(data.email);
                        $('#emp_designation').text(data.designation);
                        $('#emp_phone').text(data.phone);
                        $('#employeeInfo').show();
                    },
                    error: function() {
                        $('#employeeInfo').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load employee details',
                            confirmButtonColor: '#4e73df'
                        });
                    }
                });
            } else {
                $('#employeeInfo').hide();
            }
        });

        // Add New User button click
        $('#addNewUser').click(function() {
            resetForm();
            $('#userModalLabel').text('Add New User');
            $('#userForm').attr('action', '{{ route("admin.users.store") }}');
            $('#password').prop('required', true);
            $('#password_confirmation').prop('required', true);
            $('.text-muted').show();
            $('#methodField').remove();
            loadEmployeesAndRoles();
            $('#userModal').modal('show');
        });

        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            resetForm();

            Swal.fire({
                title: 'Loading...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ url('admin/users') }}/" + id + "/edit",
                type: 'GET',
                success: function(data) {
                    Swal.close();

                    $('#userModalLabel').text('Edit User');
                    $('#userForm').attr('action', '{{ url("admin/users") }}/' + id);

                    if ($('#methodField').length === 0) {
                        $('#userForm').append('<input type="hidden" name="_method" id="methodField" value="PUT">');
                    } else {
                        $('#methodField').val('PUT');
                    }

                    $('#user_id').val(data.user.id);

                    // Populate employees
                    $('#employee_id').empty().append('<option value="">Select an employee</option>');
                    $.each(data.employees, function(key, employee) {
                        let selected = employee.id == data.user.employee_id ? 'selected' : '';
                        $('#employee_id').append(`<option value="${employee.id}" ${selected}>${employee.name} (${employee.email})</option>`);
                    });

                    // Trigger change to show employee info
                    $('#employee_id').trigger('change');

                    // Populate roles (multiple)
                    $('#roles').empty();
                    $.each(data.roles, function(key, role) {
                        let selected = data.user_roles.includes(role.id) ? 'selected' : '';
                        $('#roles').append(`<option value="${role.id}" ${selected}>${role.name}</option>`);
                    });
                    $('#roles').trigger('change');

                    $('#status').val(data.user.status);
                    $('#password').prop('required', false);
                    $('#password_confirmation').prop('required', false);
                    $('.text-muted').show();

                    $('#userModal').modal('show');
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load user data',
                        confirmButtonColor: '#4e73df'
                    });
                }
            });
        });

        // Form submission
        $('#userForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = form.serialize();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            $('#formErrors').addClass('d-none').empty();

            Swal.fire({
                title: 'Saving...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.close();
                    $('#userModal').modal('hide');
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#4e73df',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.close();

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<ul class="mb-0">';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                            if (key === 'roles') {
                                $('#roles').addClass('is-invalid');
                                $('#roles').siblings('.invalid-feedback').text(value[0]);
                            } else {
                                $('#' + key).addClass('is-invalid');
                                $('#' + key).siblings('.invalid-feedback').text(value[0]);
                            }
                        });
                        errorHtml += '</ul>';
                        $('#formErrors').removeClass('d-none').html(errorHtml);

                        $('html, body').animate({
                            scrollTop: $('#formErrors').offset().top - 100
                        }, 500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong!',
                            confirmButtonColor: '#4e73df'
                        });
                    }
                }
            });
        });

        // Delete button click with SweetAlert
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var deleteUrl = "{{ url('admin/users') }}/" + id;

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete user "${name}". This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        }
                    }).then(response => {
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage('Request failed: ' + (error.responseJSON?.message || 'Something went wrong'));
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: result.value.message,
                        confirmButtonColor: '#4e73df',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });

        // Reset form function
        function resetForm() {
            $('#userForm')[0].reset();
            $('#user_id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            $('#formErrors').addClass('d-none').empty();
            $('#employeeInfo').hide();
            $('#employee_id').val('').trigger('change');
            $('#roles').val(null).trigger('change');
        }

        // Modal hidden event
        $('#userModal').on('hidden.bs.modal', function() {
            resetForm();
            $('#methodField').remove();
        });
    });
</script>
@endsection
