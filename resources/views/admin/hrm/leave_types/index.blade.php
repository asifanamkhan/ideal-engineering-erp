{{-- resources/views/admin/hrm/leave_types/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-calendar-alt me-2"></i> Leave Types</h4>
            </div>
            <div>
                <a href="#" class="btn btn-primary shadow-sm px-5" id="addNewLeaveType">
                    <i class="fas fa-plus me-2"></i> Add Leave Type
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="leave-types-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Name</th>
                            <th width="10%">Code</th>
                            <th width="15%">Type</th>
                            <th width="15%">Max Days/Year</th>
                            <th width="10%">Status</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="leaveTypeModal" tabindex="-1" aria-labelledby="leaveTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="leaveTypeModalLabel">Add Leave Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leaveTypeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="leave_type_id" name="leave_type_id">

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="code" name="code" placeholder="SL, CL, AL">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_paid" class="form-label">Leave Type</label>
                                <select class="form-control" id="is_paid" name="is_paid">
                                    <option value="1">Paid Leave</option>
                                    <option value="0">Unpaid Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_days_per_year" class="form-label">Max Days/Year</label>
                                <input type="number" class="form-control" id="max_days_per_year" name="max_days_per_year" value="0" min="0">
                                <small class="text-muted">0 = Unlimited</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#leave-types-sidebar').addClass('active');

    $(document).ready(function() {
        var table = $('#leave-types-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hrm.leave-types.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'code', name: 'code' },
                { data: 'is_paid_badge', name: 'is_paid', orderable: false, searchable: false, className: 'text-center' },
                { data: 'max_days_per_year', name: 'max_days_per_year', className: 'text-center' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            responsive: true
        });

        // Add New
        $('#addNewLeaveType').click(function() {
            resetForm();
            $('#leaveTypeModalLabel').text('Add Leave Type');
            $('#leaveTypeForm').attr('action', '{{ route("admin.hrm.leave-types.store") }}');
            $('#methodField').remove();
            $('#leaveTypeModal').modal('show');
        });

        // Edit
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            resetForm();

            Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: "{{ url('admin/hrm/leave-types') }}/" + id + "/edit",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    $('#leaveTypeModalLabel').text('Edit Leave Type');
                    $('#leaveTypeForm').attr('action', '{{ url("admin/hrm/leave-types") }}/' + id);

                    if ($('#methodField').length === 0) {
                        $('#leaveTypeForm').append('<input type="hidden" name="_method" id="methodField" value="PUT">');
                    } else {
                        $('#methodField').val('PUT');
                    }

                    $('#leave_type_id').val(data.id);
                    $('#name').val(data.name);
                    $('#code').val(data.code);
                    $('#description').val(data.description);
                    $('#is_paid').val(data.is_paid);
                    $('#max_days_per_year').val(data.max_days_per_year);
                    $('#status').val(data.status);
                    $('#leaveTypeModal').modal('show');
                },
                error: function() {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to load data' });
                }
            });
        });

        // Form Submit
        $('#leaveTypeForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = form.serialize();

            $('.is-invalid').removeClass('is-invalid');
            $('#formErrors').addClass('d-none');

            Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.close();
                    $('#leaveTypeModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false });
                },
                error: function(xhr) {
                    Swal.close();
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key).addClass('is-invalid');
                            $('#' + key).siblings('.invalid-feedback').text(value[0]);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.error || 'Something went wrong!' });
                    }
                }
            });
        });

        // Delete
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ url('admin/hrm/leave-types') }}/" + id,
                        type: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" }
                    }).then(response => response).catch(error => {
                        Swal.showValidationMessage(error.responseJSON?.error || 'Delete failed');
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value?.success) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: result.value.message, timer: 2000, showConfirmButton: false });
                }
            });
        });

        function resetForm() {
            $('#leaveTypeForm')[0].reset();
            $('#leave_type_id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('#formErrors').addClass('d-none');
        }
    });
</script>
@endsection
