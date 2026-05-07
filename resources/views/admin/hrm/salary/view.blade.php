{{-- resources/views/admin/hrm/salary/view.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<style>
    .salary-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .salary-summary h6 {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .salary-summary h3 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 0;
    }
    .info-card {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 15px;
        height: 100%;
    }
    .info-card label {
        font-size: 11px;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 5px;
    }
    .info-card .value {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
    }
    .employee-table th {
        background-color: #f8f9fc;
        font-weight: 600;
        text-align: center;
    }
    .employee-table td {
        vertical-align: middle;
    }
    .status-paid {
        background-color: #d4edda;
        color: #155724;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 600;
        display: inline-block;
    }
    .status-draft {
        background-color: #fff3cd;
        color: #856404;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 600;
        display: inline-block;
    }
    .status-generated {
        background-color: #cce5ff;
        color: #004085;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 600;
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i> Salary Details</h4>
                <small class="text-muted">View salary information for {{ $monthName }}</small>
            </div>
            <div>
                <a href="{{ route('admin.hrm.salary.index') }}" class="btn btn-secondary shadow-sm px-4">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="salary-summary text-white">
                <h6>Total Salary</h6>
                <h3>৳ {{ number_format($salaryRecord->total_salary, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="salary-summary text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <h6>Paid Amount</h6>
                <h3>৳ {{ number_format($salaryRecord->paid_amount, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="salary-summary text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <h6>Due Amount</h6>
                <h3>৳ {{ number_format($salaryRecord->total_salary - $salaryRecord->paid_amount, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="salary-summary text-white" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);">
                <h6>Total Employees</h6>
                <h3>{{ $salaryDetails->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-card">
                <label>Month / Year</label>
                <div class="value">{{ $monthName }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-card">
                <label>Generated Date</label>
                <div class="value">{{ date('d M Y', strtotime($salaryRecord->generated_date)) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-card">
                <label>Status</label>
                <div class="value">
                    @if($salaryRecord->status == 'paid')
                        <span class="status-paid">✓ PAID</span>
                    @elseif($salaryRecord->status == 'generated')
                        <span class="status-generated">📄 GENERATED</span>
                    @else
                        <span class="status-draft">📝 DRAFT</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-card">
                <label>Paid Date</label>
                <div class="value">{{ $salaryRecord->paid_date ? date('d M Y', strtotime($salaryRecord->paid_date)) : 'Not paid yet' }}</div>
            </div>
        </div>
    </div>

    <!-- Employee Salary Details Table -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-users me-2"></i> Employee Salary Breakdown</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered employee-table" id="employee-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Employee Info</th>
                            <th width="10%">Basic Salary</th>
                            <th width="8%">Allowance</th>
                            <th width="8%">Deduction</th>
                            <th width="8%">Unpaid Days</th>
                            <th width="10%">Unpaid Deduction</th>
                            <th width="10%">Gross Salary</th>
                            <th width="10%">Net Salary</th>
                            <th width="12%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaryDetails as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-start">
                                <div>
                                    <strong>{{ $detail->name }}</strong><br>
                                    <small class="text-muted">ID: {{ $detail->employee_code }}</small><br>
                                    <small class="text-muted">{{ $detail->designation ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td class="text-end">৳ {{ number_format($detail->basic_salary, 2) }}</td>
                            <td class="text-end">৳ {{ number_format($detail->total_allowance, 2) }}</td>
                            <td class="text-end">৳ {{ number_format($detail->total_deduction, 2) }}</td>
                            <td class="text-center">
                                @if($detail->unpaid_leave_days > 0)
                                    <span class="badge bg-danger">{{ $detail->unpaid_leave_days }} days</span>
                                @else
                                    <span class="badge bg-success">0 days</span>
                                @endif
                            </td>
                            <td class="text-end text-danger">৳ {{ number_format($detail->unpaid_leave_deduction, 2) }}</td>
                            <td class="text-end">৳ {{ number_format($detail->gross_salary, 2) }}</td>
                            <td class="text-end fw-bold text-success">৳ {{ number_format($detail->net_salary, 2) }}</td>
                            <td class="text-start">{{ $detail->remarks ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background-color: #f8f9fc; font-weight: bold;">
                        <tr>
                            <td colspan="8" class="text-end">Grand Total:</td>
                            <td class="text-end">৳ {{ number_format($salaryRecord->total_salary, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#salary-records-sidebar').addClass('active');
</script>
@endsection
