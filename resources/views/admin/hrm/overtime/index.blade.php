{{-- resources/views/admin/hrm/overtime/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .stats-card h6 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stats-card h3 {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-clock me-2"></i> Overtime Records</h4>
            </div>
            <div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle px-4" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus me-2"></i> Add Overtime
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.hrm.overtime.date-wise') }}">
                                <i class="fas fa-calendar-day me-2 text-primary"></i> Date-wise Entry
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.hrm.overtime.employee-wise') }}">
                                <i class="fas fa-user me-2 text-success"></i> Employee-wise Entry
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="overtime-table" width="100%">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Date</th>
                            <th width="10%">Employees</th>
                            <th width="15%">Total Amount</th>
                            <th width="12%">Paid Amount</th>
                            <th width="12%">Due Amount</th>
                            <th width="10%">Status</th>
                            <th width="12%">Generated</th>
                            <th width="9%">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Overtime Modal -->
<div class="modal fade" id="viewOvertimeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Overtime Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewOvertimeContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="markAsPaidBtn">
                    <i class="fas fa-check-circle me-2"></i> Mark as Paid
                </button>
            </div>
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
    $('#overtime-sidebar').addClass('active');

    var table = $('#overtime-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.hrm.overtime.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'overtime_date', name: 'overtime_date' },
            { data: 'employees_count_badge', name: 'employees_count', orderable: false, className: 'text-center' },
            { data: 'total_amount_formatted', name: 'total_amount', className: 'text-end' },
            { data: 'paid_amount_formatted', name: 'paid_amount', className: 'text-end' },
            { data: 'due_amount_formatted', name: 'due_amount', className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false, className: 'text-center' },
            { data: 'generated_date', name: 'generated_date' },
            { data: 'action', name: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        responsive: true
    });

    let currentDate = null;

    $(document).on('click', '.view-overtime-btn', function() {
    var date = $(this).data('date');

    $('#viewOvertimeModal').modal('show');
    $('#viewOvertimeContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading overtime details for ${date}...</p>
        </div>
    `);

    $.ajax({
        url: "{{ url('admin/hrm/overtime/view-by-date') }}/" + date,
        type: 'GET',
        success: function(response) {
            $('#viewOvertimeContent').html(response);
        },
        error: function(xhr) {
            var errorMsg = xhr.responseJSON?.error || 'Failed to load data';
            $('#viewOvertimeContent').html(`
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorMsg}
                </div>
            `);
        }
    });
});

$(document).on('click', '.delete-date-btn', function() {
    var date = $(this).data('date');

    Swal.fire({
        title: 'Are you sure?',
        text: "All overtime records for " + date + " will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        confirmButtonText: 'Yes, delete all!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/hrm/overtime/delete-by-date') }}/" + date,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    Swal.fire('Deleted!', response.message, 'success');
                    table.ajax.reload();
                    $('#viewOvertimeModal').modal('hide');
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to delete', 'error');
                }
            });
        }
    });
});

$('#markAsPaidBtn').click(function() {
    var date = $('.view-overtime-btn').data('date');
    if (!date) return;

    Swal.fire({
        title: 'Mark as Paid?',
        text: "This overtime record will be marked as PAID.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, mark as paid!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/hrm/overtime/mark-as-paid') }}/" + date,
                type: 'POST',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    Swal.fire('Success!', response.message, 'success');
                    table.ajax.reload();
                    $('#viewOvertimeModal').modal('hide');
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to update', 'error');
                }
            });
        }
    });
});
</script>
@endsection
