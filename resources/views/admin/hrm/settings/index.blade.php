{{-- resources/views/admin/hrm/settings/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-cog me-2"></i> HRM Settings</h4>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="settingsForm">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Default Check In Time</label>
                            <input type="time" class="form-control" name="default_check_in" id="default_check_in" value="{{ $setting->default_check_in ?? '09:00' }}">
                            <small class="text-muted">Default time for daily attendance check in</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Default Check Out Time</label>
                            <input type="time" class="form-control" name="default_check_out" id="default_check_out" value="{{ $setting->default_check_out ?? '17:00' }}">
                            <small class="text-muted">Default time for daily attendance check out</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Default Overtime Hours</label>
                            <input type="number" step="0.5" class="form-control" name="default_overtime_hour" id="default_overtime_hour" value="{{ $setting->default_overtime_hour ?? 4 }}">
                            <small class="text-muted">Default hours per day for overtime</small>
                        </div>
                    </div>
                </div>

                <hr>
                <h6 class="text-primary mb-3"><i class="fas fa-clock me-2"></i> Late/Early Settings</h6>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Late Grace Minutes</label>
                            <input type="number" step="1" class="form-control" name="late_grace_minutes" id="late_grace_minutes" value="{{ $setting->late_grace_minutes ?? 15 }}">
                            <small class="text-muted">Minutes allowed after check-in time</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Early Grace Minutes</label>
                            <input type="number" step="1" class="form-control" name="early_grace_minutes" id="early_grace_minutes" value="{{ $setting->early_grace_minutes ?? 15 }}">
                            <small class="text-muted">Minutes allowed before check-out time</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Working Hours/Day</label>
                            <input type="number" step="1" class="form-control" name="working_hours_per_day" id="working_hours_per_day" value="{{ $setting->working_hours_per_day ?? 8 }}">
                            <small class="text-muted">Hours per working day</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Late Hours for Full Day Deduction</label>
                            <input type="number" step="1" class="form-control" name="late_hours_for_full_day_deduction" id="late_hours_for_full_day_deduction" value="{{ $setting->late_hours_for_full_day_deduction ?? 8 }}">
                            <small class="text-muted">How many late hours = 1 full day deduction</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Late Deduction</label>
                            <select class="form-control" name="late_deduction_enabled" id="late_deduction_enabled">
                                <option value="1" {{ ($setting->late_deduction_enabled ?? 1) == 1 ? 'selected' : '' }}>Enable</option>
                                <option value="0" {{ ($setting->late_deduction_enabled ?? 1) == 0 ? 'selected' : '' }}>Disable</option>
                            </select>
                            <small class="text-muted">Deduct salary for late/early attendance</small>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save me-2"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#hrm-settings-sidebar').addClass('active');

    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Saving...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.settings.update') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                default_check_in: $('#default_check_in').val(),
                default_check_out: $('#default_check_out').val(),
                default_overtime_hour: $('#default_overtime_hour').val(),
                late_grace_minutes: $('#late_grace_minutes').val(),
                early_grace_minutes: $('#early_grace_minutes').val(),
                working_hours_per_day: $('#working_hours_per_day').val(),
                late_deduction_enabled: $('#late_deduction_enabled').val(),
                late_hours_for_full_day_deduction: $('#late_hours_for_full_day_deduction').val()
            },
            success: function(response) {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMsg = '';
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + '\n';
                    });
                    Swal.fire('Error!', errorMsg, 'error');
                } else {
                    Swal.fire('Error!', 'Something went wrong!', 'error');
                }
            }
        });
    });
</script>
@endsection
