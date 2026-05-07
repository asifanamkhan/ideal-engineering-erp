{{-- resources/views/admin/hrm/salary/generate.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .salary-table th {
        font-weight: 600;
        text-align: center;
    }

    .salary-table td {
        vertical-align: middle;
        text-align: center;
    }

    .editable-input {
        background-color: #fff3cd;
    }

    .total-row {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .unpaid-deduction {
        color: #dc3545;
    }


    .modal-xl {
        max-width: 800px;
    }

    .modal-backdrop {
        z-index: 1040;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-calculator me-2"></i> Generate Salary</h4>
            </div>
            <div>
                <a href="{{ route('admin.hrm.salary.index') }}" class="btn btn-secondary shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month <span class="text-danger">*</span></label>
                    <input type="month" id="month_year" class="form-control" value="{{ date('Y-m') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadDetailsBtn" class="btn btn-primary w-100">
                        <i class="fas fa-refresh me-2"></i> Load Details
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <div>
                        <span id="processingMsg" class="text-muted"></span>
                    </div>
                </div>
            </div>

            <div id="salaryContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered salary-table" id="salary-table">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">#</th>
                                <th width="18%">Employee Info</th>
                                <th width="10%">Basic Salary</th>
                                <th width="8%">Allowance</th>
                                <th width="8%">Deduction</th>
                                <th width="12%">Unpaid Deduction</th>
                                <th width="10%">Gross Salary</th>
                                <th width="10%">Net Salary</th>
                                <th width="12%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="salary-tbody">
                            <tr>
                                <td colspan="9" class="text-center">Select month and click Load Details</td>
                            </tr>
                        </tbody>
                        <tfoot id="salary-tfoot" style="display: none;">
                            <tr>
                                <td colspan="7" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold" id="grandTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="button" id="saveSalaryBtn" class="btn btn-lg btn-success px-5">
                            <i class="fas fa-save me-2"></i> Save Salary
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unpaid Leave History Modal -->
<div class="modal fade" id="unpaidHistoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-times me-2"></i> Unpaid Leave History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <strong>Employee:</strong> <span id="modal_employee_name"></span><br>
                                <strong>Employee ID:</strong> <span id="modal_employee_id"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <strong>Month:</strong> <span id="modal_month"></span><br>
                                <strong>Total Unpaid Days:</strong> <span id="modal_total_days"
                                    class="text-danger fw-bold"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loader -->
                <div id="unpaidHistoryLoader" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <p class="mt-2 text-muted">Loading unpaid history...</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm " id="unpaid-history-table">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Date</th>
                                <th width="20%">Type</th>
                                <th width="50%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="unpaid-history-tbody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">Click view button to load data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for submitting -->
<form id="salaryForm" method="POST">
    @csrf
    <input type="hidden" name="month_year" id="form_month_year">
    <input type="hidden" name="salary_record_id" id="salary_record_id">  <!-- Add this -->
    <input type="hidden" name="salary_data" id="form_salary_data">
</form>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#salary-generate-sidebar').addClass('active');

    let salaryData = [];
    let currentEmployeeId = null;
    let currentMonth = null;

    function calculateNetSalary(row) {
        var basic = Math.round(parseFloat($(row).find('.basic-salary').val())) || 0;
        var allowance = Math.round(parseFloat($(row).find('.allowance').val())) || 0;
        var deduction = Math.round(parseFloat($(row).find('.deduction').val())) || 0;
        var unpaidDeduction = Math.round(parseFloat($(row).find('.unpaid-deduction').val())) || 0;

        // DO NOT recalculate unpaid days from deduction
        // Keep original unpaid days from hidden field
        var unpaidDays = parseInt($(row).find('.unpaid-leave-days').val()) || 0;

        var grossSalary = (basic + allowance) - deduction;
        var netSalary = grossSalary - unpaidDeduction;

        $(row).find('.gross-salary').val(Math.round(grossSalary));
        $(row).find('.net-salary').val(Math.round(netSalary));

        var index = $(row).data('index');
        if (salaryData[index]) {
            salaryData[index].basic_salary = basic;
            salaryData[index].total_allowance = allowance;
            salaryData[index].total_deduction = deduction;
            salaryData[index].unpaid_leave_days = unpaidDays;  // Keep original
            salaryData[index].unpaid_deduction = unpaidDeduction;
            salaryData[index].gross_salary = Math.round(grossSalary);
            salaryData[index].net_salary = Math.round(netSalary);
            salaryData[index].remarks = $(row).find('.remarks-input').val();
        }

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        var total = 0;
        $('.net-salary').each(function() {
            total += Math.round(parseFloat($(this).val()) || 0);
        });
        $('#grandTotal').text(Math.round(total));
    }

    $('#loadDetailsBtn').click(function() {
        var monthYear = $('#month_year').val();

        if (!monthYear) {
            Swal.fire('Error', 'Please select a month', 'error');
            return;
        }

        currentMonth = monthYear;

        Swal.fire({
            title: 'Loading...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.salary.generate-details') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                month_year: monthYear
            },
            success: function(response) {
                Swal.close();
                salaryData = response.map(function(emp) {
                    return {
                        ...emp,
                        basic_salary: Math.round(emp.basic_salary),
                        total_allowance: Math.round(emp.total_allowance),
                        total_deduction: Math.round(emp.total_deduction),
                        unpaid_leave_days: Math.round(emp.unpaid_leave_days),
                        unpaid_deduction: Math.round(emp.unpaid_deduction),
                        gross_salary: Math.round(emp.gross_salary),
                        net_salary: Math.round(emp.net_salary)
                    };
                });
                renderSalaryTable();
                $('#salaryContainer').show();
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Failed to load data', 'error');
            }
        });
    });

    function renderSalaryTable() {
    var tbody = $('#salary-tbody');
    tbody.empty();

    if (salaryData.length === 0) {
        tbody.append('<tr><td colspan="9" class="text-center text-muted">No employees found</td></tr>');
        $('#salary-tfoot').hide();
        return;
    }

    $.each(salaryData, function(index, emp) {
        var unpaidDays = emp.unpaid_leave_days || 0;  // Store original unpaid days
        var remarks = emp.remarks || '';

        var row = `
            <tr data-index="${index}" data-employee-id="${emp.employee_id}">
                <td class="text-center">${index + 1}</td>
                <td style="text-align: left;">
                    <div>
                        <strong>${escapeHtml(emp.employee_name)}</strong><br>
                        ID:<small class="text-muted">ID: ${emp.employee_code}</small>,
                        DES:<small class="text-muted">${emp.designation}</small>
                    </div>
                </td>
                <td>
                    <input type="number" step="1" class="form-control  basic-salary text-end editable-input" value="${Math.round(emp.basic_salary)}" style="width: 120px;">
                </td>
                <td>
                    <input type="number" step="1" class="form-control  allowance text-end editable-input" value="${Math.round(emp.total_allowance)}" style="width: 100px;">
                </td>
                <td>
                    <input type="number" step="1" class="form-control  deduction text-end editable-input" value="${Math.round(emp.total_deduction)}" style="width: 100px;">
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center " style="gap: 4px">
                        <input type="number" step="1" class="form-control  unpaid-deduction text-end editable-input" value="${Math.round(emp.unpaid_deduction)}" style="width: 100px;">
                        <button type="button" class="btn btn-outline-primary btn-view-details" data-employee-id="${emp.employee_id}" data-employee-name="${escapeHtml(emp.employee_name)}" data-employee-code="${emp.employee_code}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <input type="hidden" class="unpaid-leave-days" value="${unpaidDays}">
                 </td>
                <td>
                    <input type="text" class="form-control  gross-salary text-end" value="${Math.round(emp.gross_salary)}" readonly style="background:#e9ecef; width: 110px;">
                </td>
                <td>
                    <input type="text" class="form-control  net-salary text-end fw-bold" value="${Math.round(emp.net_salary)}" readonly style="background:#e9ecef; width: 110px; color:#28a745;">
                    <input type="hidden" class="employee-id" value="${emp.employee_id}">
                </td>
                <td>
                    <textarea class="form-control  remarks-input" placeholder="Adjustment remarks..." rows="2" style="width: 150px;">${escapeHtml(remarks)}</textarea>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Bind events
    $('.basic-salary, .allowance, .deduction, .unpaid-deduction').on('input', function() {
        calculateNetSalary($(this).closest('tr'));
    });

    $('.btn-view-details').on('click', function() {
        var employeeId = $(this).data('employee-id');
        var employeeName = $(this).data('employee-name');
        var employeeCode = $(this).data('employee-code');
        showUnpaidHistory(employeeId, employeeName, employeeCode);
    });

    calculateGrandTotal();
    $('#salary-tfoot').show();
}
    function showUnpaidHistory(employeeId, employeeName, employeeCode) {
    $('#modal_employee_name').text(employeeName);
    $('#modal_employee_id').text(employeeCode);
    $('#modal_month').text(currentMonth ? new Date(currentMonth + '-01').toLocaleDateString('en', { year: 'numeric', month: 'long' }) : '');

    // Clear table and show loader
    $('#unpaid-history-tbody').html('');
    $('#unpaidHistoryLoader').show();
    $('#unpaid-history-table').hide();

    // Show modal first with loader
    $('#unpaidHistoryModal').modal('show');

    $.ajax({
        url: "{{ route('admin.hrm.salary.get-unpaid-history') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            employee_id: employeeId,
            month_year: currentMonth
        },
        beforeSend: function() {
            $('#unpaidHistoryLoader').show();
            $('#unpaid-history-table').hide();
            $('#unpaid-history-tbody').html('');
        },
        success: function(response) {
            $('#modal_total_days').text(response.total_unpaid_days);

            var tbody = $('#unpaid-history-tbody');
            tbody.empty();

            if (response.history.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center text-muted">No unpaid leave records found</td></tr>');
            } else {
                $.each(response.history, function(index, record) {
                    var typeBadge = record.type === 'Leave Application' ?
                        '<span class="badge bg-info">Leave Application</span>' :
                        '<span class="badge bg-warning text-dark">Unpaid Absence</span>';

                    var row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${record.date}</td>
                            <td class="text-center">${typeBadge}</td>
                            <td>${escapeHtml(record.remarks) || '-'}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }

            // Hide loader and show table
            $('#unpaidHistoryLoader').hide();
            $('#unpaid-history-table').show();
        },
        error: function(xhr) {
            $('#unpaidHistoryLoader').hide();
            $('#unpaid-history-table').show();
            var errorMsg = xhr.responseJSON?.error || 'Failed to load history';
            $('#unpaid-history-tbody').html('<tr><td colspan="4" class="text-center text-danger">' + errorMsg + '</td></tr>');
        }
    });
}

    $('#saveSalaryBtn').click(function() {
        var monthYear = $('#month_year').val();
    var salaryDataToSave = [];
    var salaryRecordId = $('#salary_record_id').val();

    $('.salary-table tbody tr').each(function() {
        var row = $(this);
        salaryDataToSave.push({
            employee_id: row.find('.employee-id').val(),
            basic_salary: Math.round(parseFloat(row.find('.basic-salary').val()) || 0),
            total_allowance: Math.round(parseFloat(row.find('.allowance').val()) || 0),
            total_deduction: Math.round(parseFloat(row.find('.deduction').val()) || 0),
            unpaid_leave_days: parseInt(row.find('.unpaid-leave-days').val()) || 0,  // Keep original
            unpaid_deduction: Math.round(parseFloat(row.find('.unpaid-deduction').val()) || 0),
            gross_salary: Math.round(parseFloat(row.find('.gross-salary').val()) || 0),
            net_salary: Math.round(parseFloat(row.find('.net-salary').val()) || 0),
            remarks: row.find('.remarks-input').val() || null
        });
    });

        if (salaryDataToSave.length === 0) {
            Swal.fire('Error', 'No salary data to save', 'error');
            return;
        }

        Swal.fire({
            title: 'Saving...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('admin.hrm.salary.store') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                month_year: monthYear,
                salary_data: salaryDataToSave
            },
            success: function(response) {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    window.location.href = "{{ route('admin.hrm.salary.index') }}";
                });
            },
            error: function(xhr) {
                Swal.close();
                var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save salary';
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
</script>
@endsection
