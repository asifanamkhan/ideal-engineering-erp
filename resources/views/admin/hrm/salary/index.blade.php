{{-- resources/views/admin/hrm/salary/index.blade.php --}}

@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
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
                <h4 class="mb-1"><i class="fas fa-money-bill-wave me-2"></i> Salary Records</h4>
            </div>
            <div>
                <a href="{{ route('admin.hrm.salary.create') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-plus me-2"></i> Generate Salary
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="salary-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Month</th>
                            <th width="10%">Employees</th>
                            <th width="15%">Total Salary</th>
                            <th width="12%">Paid Amount</th>
                            <th width="12%">Due Amount</th>
                            <th width="10%">Status</th>
                            <th width="12%">Generated Date</th>
                            <th width="9%">Actions</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#hrm-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');
    $('#salary-records-sidebar').addClass('active');

    var table = $('#salary-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.hrm.salary.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'month_year', name: 'month_year' },
            { data: 'employees_count', name: 'employees_count', className: 'text-center' },
            { data: 'total_salary_formatted', name: 'total_salary' },
            { data: 'paid_amount_formatted', name: 'paid_amount' },
            { data: 'due_amount_formatted', name: 'due_amount' },
            { data: 'status_badge', name: 'status', orderable: false, className: 'text-center' },
            { data: 'generated_date_formatted', name: 'generated_date' },
            { data: 'action', name: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });

    // View button
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        window.open("{{ url('admin/hrm/salary') }}/" + id, '_blank');
    });

    // Edit button
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = "{{ url('admin/hrm/salary') }}/" + id + "/edit";
    });

    // Mark as Paid button
    $(document).on('click', '.mark-paid-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Mark as Paid?',
            text: "This salary record will be marked as PAID.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, mark as paid!',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                return $.ajax({
                    url: "{{ url('admin/hrm/salary/mark-paid') }}/" + id,
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}" }
                }).then(response => response).catch(error => {
                    Swal.showValidationMessage(error.responseJSON?.error || 'Failed');
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value?.success) {
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Success!', text: result.value.message, timer: 2000, showConfirmButton: false });
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
                    url: "{{ url('admin/hrm/salary') }}/" + id,
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

    // View button
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        window.open("{{ url('admin/hrm/salary') }}/" + id, '_blank');
    });
</script>
@endsection
