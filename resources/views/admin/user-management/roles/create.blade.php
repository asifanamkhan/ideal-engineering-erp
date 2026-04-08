@extends('layouts.dashboard.app')

@section('css')
@include('admin.user-management.roles.partials.css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-user"></i> Create New Role</h4>
            </div>
            <div>
                <a href="{{ route('admin.roles.index') }}" class="btn px-5 btn-primary shadow-sm btn">
                    <i class="fas fa-arrow-left"></i> Back to Roles
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
                        @csrf

                        {{-- Role Name Field --}}
                        <div class="form-group mb-4">
                            <label for="name" class="form-label fw-bold">Role Name</label>
                            <input type="text"
                                    name="name"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter role name"
                                    value="{{ old('name') }}"
                                    required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Permissions Section --}}
                        <div class="form-group mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-label fw-bold mb-0">Permissions</label>
                                <div class="global-select-all">
                                    <button type="button" class="global-select-all-btn" id="selectAllModules">
                                        <i class="fas fa-check-double"></i> Select All Modules
                                    </button>
                                </div>
                            </div>

                            @php
                                $modules = App\Models\Module::with('permissions')->get();
                            @endphp

                            @foreach($modules as $module)
                                <div class="permission-card">
                                    <div class="module-title">
                                        <h6>{{ ucfirst($module->name) }}</h6>
                                        <button type="button" class="select-all-btn" data-module="{{ $module->id }}">
                                            Select All
                                        </button>
                                    </div>
                                    <div class="permissions-list">
                                        @foreach($module->permissions as $permission)
                                            <div class="permission-item">
                                                <span class="permission-label">
                                                    {{ ucfirst(str_replace('_', ' ', $permission->name)) }}
                                                </span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $permission->id }}"
                                                            class="permission-checkbox permission-module-{{ $module->id }}"
                                                            {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            @error('permissions')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                <i class="fas fa-save"></i> Create Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-lg px-5">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $('#user-management-sidebar').addClass('active');
    $('#roles-index-sidebar').addClass('active');
    $('#collapseUserManagement').addClass('show');

    $(document).ready(function() {
        // Global Select All Modules functionality
        $('#selectAllModules').on('click', function() {
            const allCheckboxes = $('.permission-checkbox');
            const totalCheckboxes = allCheckboxes.length;
            const checkedCheckboxes = allCheckboxes.filter(':checked').length;

            // If all are selected, deselect all; otherwise select all
            const selectAll = checkedCheckboxes !== totalCheckboxes;

            allCheckboxes.prop('checked', selectAll);

            // Update all module-specific select all buttons
            $('.select-all-btn').each(function() {
                const moduleId = $(this).data('module');
                const moduleCheckboxes = $(`.permission-module-${moduleId}`);
                const allModuleChecked = moduleCheckboxes.length === moduleCheckboxes.filter(':checked').length;

                if (allModuleChecked && moduleCheckboxes.length > 0) {
                    $(this).text('Deselect All');
                } else {
                    $(this).text('Select All');
                }
            });

            // Update global button text
            if (selectAll) {
                $(this).html('<i class="fas fa-times"></i> Deselect All Modules');
                $(this).removeClass('btn-success').addClass('btn-danger');
            } else {
                $(this).html('<i class="fas fa-check-double"></i> Select All Modules');
                $(this).removeClass('btn-danger').addClass('btn-success');
            }
        });

        // Select All functionality for each module
        $('.select-all-btn').on('click', function() {
            const moduleId = $(this).data('module');
            const checkboxes = $(`.permission-module-${moduleId}`);
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;

            checkboxes.prop('checked', !allChecked);

            // Change button text based on state
            if (!allChecked) {
                $(this).text('Deselect All');
            } else {
                $(this).text('Select All');
            }

            // Update global select all button state
            updateGlobalSelectAllButton();
        });

        // Update button text when individual checkboxes change
        $('.permission-checkbox').on('change', function() {
            const moduleId = $(this).attr('class').match(/permission-module-(\d+)/)[1];
            const checkboxes = $(`.permission-module-${moduleId}`);
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
            const selectAllBtn = $(`.select-all-btn[data-module="${moduleId}"]`);

            if (allChecked) {
                selectAllBtn.text('Deselect All');
            } else {
                selectAllBtn.text('Select All');
            }

            // Update global select all button state
            updateGlobalSelectAllButton();
        });

        // Function to update global select all button state
        function updateGlobalSelectAllButton() {
            const allCheckboxes = $('.permission-checkbox');
            const totalCheckboxes = allCheckboxes.length;
            const checkedCheckboxes = allCheckboxes.filter(':checked').length;

            if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
                $('#selectAllModules').html('<i class="fas fa-times"></i> Deselect All Modules');
                $('#selectAllModules').removeClass('btn-success').addClass('btn-danger');
            } else {
                $('#selectAllModules').html('<i class="fas fa-check-double"></i> Select All Modules');
                $('#selectAllModules').removeClass('btn-danger').addClass('btn-success');
            }
        }

        // SweetAlert confirmation on form submit with normal form submission
        $('#roleForm').on('submit', function(e) {
            const checkedPermissions = $('.permission-checkbox:checked').length;
            const roleName = $('#name').val().trim();

            if (!roleName) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a role name.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            if (checkedPermissions === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select at least one permission for the role.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Show confirmation dialog
            e.preventDefault();

            Swal.fire({
                title: 'Create Role',
                text: `Are you sure you want to create "${roleName}" role?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, create it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Creating...',
                        text: 'Please wait while we create the role.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit the form normally
                    $('#roleForm').off('submit').submit();
                }
            });
        });

        // Check for session success message and show toast
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Check for session error message
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // Check for validation errors
        @if($errors->any())
            @if($errors->has('name'))
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: '{{ $errors->first('name') }}',
                    confirmButtonColor: '#3085d6'
                });
            @elseif($errors->has('permissions'))
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: '{{ $errors->first('permissions') }}',
                    confirmButtonColor: '#3085d6'
                });
            @endif
        @endif

        // Initialize button texts based on initial checked state
        $('.permission-checkbox').each(function() {
            const moduleId = $(this).attr('class').match(/permission-module-(\d+)/)[1];
            const checkboxes = $(`.permission-module-${moduleId}`);
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
            const selectAllBtn = $(`.select-all-btn[data-module="${moduleId}"]`);

            if (allChecked && checkboxes.length > 0) {
                selectAllBtn.text('Deselect All');
            }
        });

        // Initialize global select all button state
        updateGlobalSelectAllButton();

        // Style the global button properly
        $('#selectAllModules').addClass('btn-success');
    });
</script>
@endsection
