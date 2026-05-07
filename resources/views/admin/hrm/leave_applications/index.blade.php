{{-- resources/views/admin/hrm/leave_applications/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border-radius: 0.375rem;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-dropdown {
        border-radius: 0.375rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-calendar-alt me-2"></i> Leave Applications</h4>
            </div>
            <div>
                <a href="#" class="btn btn-primary shadow-sm px-5" id="applyLeaveBtn">
                    <i class="fas fa-plus me-2"></i> Apply Leave
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="leave-applications-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Employee</th>
                            <th width="15%">Leave Type</th>
                            <th width="20%">Date Range</th>
                            <th width="8%">Days</th>
                            <th width="8%">Reason</th>
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

<!-- Apply/Edit Leave Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="leaveModalLabel">Apply Leave</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="leaveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="leave_id" name="leave_id">
                    <input type="hidden" id="calculated_total_days" name="total_days">

                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select class="form-control select2-employee" id="employee_id" name="employee_id"
                            style="width: 100%;" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label">Leave Type <span
                                class="text-danger">*</span></label>
                        <select class="form-control" id="leave_type_id" name="leave_type_id" required>
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" data-max-days="{{ $type->max_days_per_year }}"
                                data-is-paid="{{ $type->is_paid }}">
                                {{ $type->name }} ({{ $type->code }})
                                @if($type->is_paid == 1) - Paid @else - Unpaid @endif
                                @if($type->max_days_per_year > 0) - Max: {{ $type->max_days_per_year }} days/year @endif
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Days: <strong id="total_days_display">0</strong> days</label>
                        <div id="balance_warning" class="text-danger small mt-1" style="display: none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"
                            placeholder="Optional: Enter reason for leave"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#leave-apply-sidebar').addClass('active');

    $(document).ready(function() {
        // Initialize Select2
        $('.select2-employee').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search employee...',
            dropdownParent: $('#leaveModal'),
            width: '100%'
        });

        var table = $('#leave-applications-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.hrm.leave-applications.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'employee_info', name: 'employee_info', orderable: false, searchable: true },
                { data: 'leave_info', name: 'leave_info', orderable: false, searchable: true },
                { data: 'date_range', name: 'date_range', orderable: false },
                { data: 'total_days', name: 'total_days', className: 'text-center' },
                { data: 'reason', name: 'reason', render: function(data) { return data || '-'; } },
                { data: 'status_badge', name: 'status', orderable: false, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true
        });

        // Client-side function to calculate total days (excluding weekends - Fri & Sat)
           
    function calculateTotalDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;

        var start = new Date(startDate);
        var end = new Date(endDate);

        if (start > end) return 0;

        // Simple difference in days
        var diffTime = Math.abs(end - start);
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        return diffDays;
    }

        // Calculate days and update display
        function updateTotalDays() {
            var start = $('#start_date').val();
            var end = $('#end_date').val();

            if (start && end) {
                var totalDays = calculateTotalDays(start, end);
                $('#total_days_display').text(totalDays);
                $('#calculated_total_days').val(totalDays);

                // Check balance after days calculation
                checkBalance();
                return totalDays;
            } else {
                $('#total_days_display').text('0');
                $('#calculated_total_days').val('0');
                return 0;
            }
        }

        // Check leave balance (client-side calculation with server balance data)
        function checkBalance() {
            var employeeId = $('#employee_id').val();
            var leaveTypeId = $('#leave_type_id').val();
            var totalDays = parseInt($('#total_days_display').text());

            if (employeeId && leaveTypeId && totalDays > 0) {
                var selectedOption = $('#leave_type_id option:selected');
                var isPaid = selectedOption.data('is-paid');
                var maxDays = selectedOption.data('max-days');

                if (isPaid == 1 && maxDays > 0) {
                    // Fetch used days from server (but only once per selection)
                    $.ajax({
                        url: "{{ url('admin/hrm/leave-applications/check-balance') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            employee_id: employeeId,
                            leave_type_id: leaveTypeId
                        },
                        success: function(response) {
                            var available = maxDays - response.used_days;
                            if (totalDays > available) {
                                $('#balance_warning').show().html('⚠️ Warning: Only ' + available + ' days available! You have used ' + response.used_days + ' of ' + maxDays + ' days.');
                            } else {
                                $('#balance_warning').hide();
                            }
                        },
                        error: function() {
                            $('#balance_warning').hide();
                        }
                    });
                } else {
                    $('#balance_warning').hide();
                }
            } else {
                $('#balance_warning').hide();
            }
        }

        // Event listeners for date changes
        $('#start_date, #end_date').on('change', function() {
            updateTotalDays();
        });

        // Also calculate when typing (for immediate feedback)
        $('#start_date, #end_date').on('input', function() {
            updateTotalDays();
        });

        $('#employee_id, #leave_type_id').on('change', function() {
            if ($('#start_date').val() && $('#end_date').val()) {
                updateTotalDays();
            }
        });

        // Apply Leave button
        $('#applyLeaveBtn').click(function() {
            resetForm();
            $('#leaveModalLabel').text('Apply Leave');
            $('#leaveForm').attr('action', '{{ route("admin.hrm.leave-applications.store") }}');
            $('#methodField').remove();
            $('#leaveModal').modal('show');
        });

        // Edit button
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            resetForm();

            Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: "{{ url('admin/hrm/leave-applications') }}/" + id + "/edit",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    $('#leaveModalLabel').text('Edit Leave Application');
                    $('#leaveForm').attr('action', '{{ url("admin/hrm/leave-applications") }}/' + id);

                    if ($('#methodField').length === 0) {
                        $('#leaveForm').append('<input type="hidden" name="_method" id="methodField" value="PUT">');
                    } else {
                        $('#methodField').val('PUT');
                    }

                    $('#leave_id').val(data.id);
                    $('#employee_id').val(data.employee_id).trigger('change');
                    $('#leave_type_id').val(data.leave_type_id);
                    $('#start_date').val(data.start_date);
                    $('#end_date').val(data.end_date);
                    $('#reason').val(data.reason);

                    // Calculate days on client side
                    updateTotalDays();
                    $('#leaveModal').modal('show');
                },
                error: function() {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to load leave data' });
                }
            });
        });

        // Approve button
        $(document).on('click', '.approve-btn', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Approve Leave?',
                text: "This leave will be approved and balance will be deducted.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ url('admin/hrm/leave-applications/approve') }}/" + id,
                        type: 'POST',
                        data: { _token: "{{ csrf_token() }}" }
                    }).then(response => response).catch(error => {
                        Swal.showValidationMessage(error.responseJSON?.error || 'Approval failed');
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value?.success) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Approved!', text: result.value.message, timer: 2000, showConfirmButton: false });
                }
            });
        });

        // Reject button
        $(document).on('click', '.reject-btn', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Reject Leave?',
                text: "This leave application will be rejected.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ url('admin/hrm/leave-applications/reject') }}/" + id,
                        type: 'POST',
                        data: { _token: "{{ csrf_token() }}" }
                    }).then(response => response).catch(error => {
                        Swal.showValidationMessage(error.responseJSON?.error || 'Rejection failed');
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value?.success) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Rejected!', text: result.value.message, timer: 2000, showConfirmButton: false });
                }
            });
        });

        // Delete button
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
                        url: "{{ url('admin/hrm/leave-applications') }}/" + id,
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

        // Form Submit
        $('#leaveForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');

            // Make sure total_days is set before submit
            var totalDays = updateTotalDays();
            if (totalDays <= 0) {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Please select valid date range' });
                return;
            }

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
                    $('#leaveModal').modal('hide');
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
                        Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.error || xhr.responseJSON?.message || 'Something went wrong!' });
                    }
                }
            });
        });

        function resetForm() {
            $('#leaveForm')[0].reset();
            $('#leave_id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('#formErrors').addClass('d-none');
            $('#total_days_display').text('0');
            $('#calculated_total_days').val('0');
            $('#balance_warning').hide();
            $('#employee_id').val('').trigger('change');
        }

        $(document).on('click', '.unapprove-btn', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Unapprove Leave?',
                text: "This leave will be moved back to pending status.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f6c23e',
                confirmButtonText: 'Yes, unapprove it!',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ url('admin/hrm/leave-applications/unapprove') }}/" + id,
                        type: 'POST',
                        data: { _token: "{{ csrf_token() }}" }
                    }).then(response => response).catch(error => {
                        Swal.showValidationMessage(error.responseJSON?.error || 'Unapprove failed');
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value?.success) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Unapproved!', text: result.value.message, timer: 2000, showConfirmButton: false });
                }
            });
        });
    });
</script>
@endsection
