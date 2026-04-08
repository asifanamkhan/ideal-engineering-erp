@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<style>
    .employee-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #4e73df;
        padding: 3px;
    }

    .info-card {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 8px;
        margin-bottom: 8px;
        border-left: 4px solid #4e73df;
    }

    .info-label {
        font-size: 12px;
        color: #858796;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 500;
        color: #2c3e50;
    }

    .salary-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .salary-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .badge-employee {
        background-color: #4e73df;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Employee Salary Info
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / employees / salary info</span>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-10">
                    <div class="form-group">
                        <label for="employee_id">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-control selectpicker"
                            data-live-search="true" required>
                            <option value="">-- Select Employee --</option>
                            @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->employee_id }} - {{ $employee->name }} ({{ $employee->designation_name ??
                                'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" id="searchBtn" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Details Section -->
    <div id="employeeDetails" style="display: none;">
        <!-- Employee info will be loaded here -->
    </div>

    <!-- Salary Form Section -->
    <div id="salaryFormSection" style="display: none;">
        <!-- Salary form will be loaded here -->
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Activate sidebar
    $('#hrm-sidebar, #employee-salary-info-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');

    $(document).ready(function() {
        // Initialize selectpicker only once
        $('.selectpicker').selectpicker();

        // Search button click
        $('#searchBtn').click(function() {
            let employeeId = $('#employee_id').val();
            
            if (!employeeId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Please select an employee first.',
                    confirmButtonColor: '#28a745'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we load employee data',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Load employee details
            $.ajax({
                url: "{{ route('admin.employee-salary.getDetails') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    employee_id: employeeId
                },
                success: function(response) {
                    Swal.close();
                    
                    // Clear and replace content
                    $('#employeeDetails').empty().html(response.details_html).show();
                    $('#salaryFormSection').empty().html(response.form_html).show();
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.error || 'Failed to load employee data.',
                        confirmButtonColor: '#28a745'
                    });
                }
            });
        });

        // Save salary form
        $(document).on('click', '#saveSalaryBtn', function() {
            let $btn = $(this);
            let originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            
            let formData = $('#salaryForm').serialize();
            
            $.ajax({
                url: "{{ route('admin.employee-salary.save') }}",
                type: 'POST',
                data: formData + '&_token={{ csrf_token() }}',
                success: function(response) {
                    if (response.success) {
                        // Update the displayed values without reloading everything
                        $('#basic_salary').val(response.employee.basic_salary);
                        $('#total_allowance').val(response.employee.total_allowance);
                        $('#total_deduction').val(response.employee.total_deduction);
                        $('#overtime_rate').val(response.employee.overtime_rate);
                        
                        // Recalculate and display
                        calculateGross();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#28a745',
                            timer: 2000,
                            showConfirmButton: true
                        });
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
                
    });
</script>
@endsection