{{-- resources/views/admin/hrm/weekend/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
                <h4 class="mb-1"><i class="fas fa-calendar-week me-2"></i> Weekend Settings</h4>
            </div>
            <div>
                <button type="button" class="btn btn-primary shadow-sm px-5" id="addWeekendBtn">
                    <i class="fas fa-plus me-2"></i> Add Weekend Day
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="weekend-table" width="100%">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="30%">Day Name</th>
                            <th width="20%">Day Number</th>
                            <th width="15%">Status</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Weekend Modal -->
<div class="modal fade" id="weekendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add Weekend Day</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="weekendForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="weekend_id" name="weekend_id">

                    <div class="mb-3">
                        <label class="form-label">Day Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="day_name" name="day_name" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Day Number <span class="text-danger">*</span></label>
                        <select class="form-control" id="day_number" name="day_number" required>
                            <option value="">Select Day Number</option>
                            <option value="1">1 (Monday)</option>
                            <option value="2">2 (Tuesday)</option>
                            <option value="3">3 (Wednesday)</option>
                            <option value="4">4 (Thursday)</option>
                            <option value="5">5 (Friday)</option>
                            <option value="6">6 (Saturday)</option>
                            <option value="7">7 (Sunday)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
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
    $('#weekend-settings-sidebar').addClass('active');

    var table = $('#weekend-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.hrm.weekend-settings.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'day_name', name: 'day_name' },
            { data: 'day_number', name: 'day_number' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false }
        ]
    });

    $('#addWeekendBtn').click(function() {
        $('#weekendForm')[0].reset();
        $('#weekend_id').val('');
        $('#weekendModalLabel').text('Add Weekend Day');
        $('#weekendForm').attr('action', '{{ route("admin.hrm.weekend-settings.store") }}');
        $('#methodField').remove();
        $('#weekendModal').modal('show');
    });

    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/hrm/weekend-settings') }}/" + id + "/edit",
            type: 'GET',
            success: function(data) {
                $('#weekend_id').val(data.id);
                $('#day_name').val(data.day_name);
                $('#day_number').val(data.day_number);
                $('#status').val(data.status);
                $('#weekendForm').attr('action', '{{ url("admin/hrm/weekend-settings") }}/' + id);
                if ($('#methodField').length === 0) {
                    $('#weekendForm').append('<input type="hidden" name="_method" id="methodField" value="PUT">');
                } else {
                    $('#methodField').val('PUT');
                }
                $('#weekendModal').modal('show');
            }
        });
    });

    $('#weekendForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = form.serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#weekendModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Success!', response.message, 'success');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('#' + key).addClass('is-invalid');
                        $('#' + key).siblings('.invalid-feedback').text(value[0]);
                    });
                } else {
                    Swal.fire('Error!', 'Something went wrong!', 'error');
                }
            }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/hrm/weekend-settings') }}/" + id,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        table.ajax.reload();
                        Swal.fire('Deleted!', response.message, 'success');
                    }
                });
            }
        });
    });
</script>
@endsection
