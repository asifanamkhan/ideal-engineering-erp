{{-- resources/views/admin/hrm/leave_applications/balance.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        font-size: 11px;
    }
    .leave-balance-grid {
        padding: 5px 0;
    }
    .leave-item {
        border-bottom: 1px solid #e3e6f0;
        padding-bottom: 8px;
        margin-bottom: 8px;
    }
    .leave-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .total-row {
        background-color: #f8f9fc;
        border-radius: 5px;
        padding: 8px;
        margin-top: 8px;
    }
    .progress {
        background-color: #e3e6f0;
        border-radius: 10px;
    }
    .table td {
        vertical-align: middle;
    }

</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-balance-scale me-2"></i> Leave Balance Report</h4>
            </div>
            <div>
                <button type="button" id="resetYearBtn" class="btn btn-warning shadow-sm px-4">
                    <i class="fas fa-sync-alt me-2"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Year</label>
                    <select id="select_year" class="form-control select2-year">
                        @php
                            $currentYear = date('Y');
                            $startYear = 2024;
                        @endphp
                        @for($year = $currentYear; $year >= $startYear; $year--)
                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                        <option value="all">All Years</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <button type="button" id="loadBalanceBtn" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Load
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="balance-table" width="100%">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Employee</th>
                            <th width="8%">Year</th>
                            <th width="12%">Designation</th>
                            <th width="50%">Leave Balance Summary</th>
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
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#leave-balance-sidebar').addClass('active');

    // Initialize Select2
    $('.select2-year').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Year',
        width: '100%'
    });

    var table = $('#balance-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.hrm.leave-applications.balance') }}",
            data: function(d) {
                d.year = $('#select_year').val();
            },
            beforeSend: function() {
                // Show custom loader
                $('.dataTables_processing').css('display', 'block');
            },
            complete: function() {
                // Hide loader
                $('.dataTables_processing').css('display', 'none');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'employee_info', name: 'employee_info', orderable: false, searchable: true },
            { data: 'year', name: 'year', className: 'text-center', width: '8%' },
            { data: 'designation', name: 'designation', defaultContent: '-', width: '12%' },
            { data: 'leave_balance_summary', name: 'leave_balance_summary', orderable: false, searchable: false, width: '50%' }
        ],
        order: [[2, 'desc'], [1, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        language: {
            processing: '',
            search: "🔍 Search Employee:",
            searchPlaceholder: "Type name or ID...",
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No employees found",
            info: "Showing _START_ to _END_ of _TOTAL_ employees",
            infoEmpty: "No employees available",
            infoFiltered: "(filtered from _MAX_ total employees)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        drawCallback: function() {
            // Hide loader after drawing
            $('.dataTables_processing').css('display', 'none');
        }
    });


    $('#loadBalanceBtn').click(function() {
        table.ajax.reload();
    });

</script>
@endsection
