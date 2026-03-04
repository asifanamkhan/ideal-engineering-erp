@extends('layouts.dashboard.app')
@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }


    .table thead {
        background: linear-gradient(135deg, #28ACE2 0%, #1E88E5 100%);
        color: white;
    }

    .dataTables_wrapper .dataTables_length select {
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .dt-buttons .btn {
        margin-right: 5px;
    }

    /* Custom alignment for DataTable controls */
    .dataTables_wrapper .dataTables_length {
        float: left;
    }

    .dataTables_wrapper .dt-buttons {
        text-align: center;
        float: none;
        margin: 0 auto;
        display: inline-block;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
    }

    /* Ensure proper row alignment */
    .dataTables_wrapper .row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .dataTables_wrapper .row:first-child {
        justify-content: space-between;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    .d-flex.gap-1>* {
        margin: 0 2px;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .status-active {
        color: #28a745;
        font-weight: bold;
    }

    .status-inactive {
        color: #dc3545;
        font-weight: bold;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Department List</h1>


            <a id="btnCreate" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add Department
            </a>

    </div>


    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="departmentTable" class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $key => $dep)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $dep->name }}</td>
                                <td>{{ $dep->code }}</td>
                                <td>{{ $dep->description }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning btnEdit" data-id="{{ $dep->id }}">Edit</button>
                                    <button class="btn btn-sm btn-danger btnDelete" data-id="{{ $dep->id }}">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="departmentForm" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="dep_id">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label>Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group mb-2">
                    <label>Code</label>
                    <input type="text" name="code" id="code" class="form-control">
                </div>
                <div class="form-group mb-2">
                    <label>Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
<!-- PDF make -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#settings-sidebar').addClass('active');
    $('#department-index-sidebar').addClass('active');
    $('#collapseSettings').addClass('show');
$(function() {
    const modal = new bootstrap.Modal('#departmentModal');

    // === INIT DATATABLE ===
    let table = $('#departmentTable').DataTable({
        responsive: true,
        processing: true,
        lengthChange: true,
        autoWidth: false,
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-secondary' },
            { extend: 'csv', className: 'btn btn-sm btn-secondary' },
            { extend: 'pdf', className: 'btn btn-sm btn-secondary' },
            { extend: 'print', className: 'btn btn-sm btn-secondary' },
        ]
    });
    table.buttons().container().appendTo('#departmentTable_wrapper .col-md-6:eq(0)');

    // === CREATE ===
    $('#btnCreate').click(function() {
        $('#departmentForm')[0].reset();
        $('#dep_id').val('');
        $('#modalTitle').text('Add Department');
        modal.show();
    });

    // === SAVE (CREATE / UPDATE) ===
    $('#departmentForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#dep_id').val();
        let url = id
            ? "{{ route('departments.update', ':id') }}".replace(':id', id)
            : "{{ route('departments.store') }}";
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1200
                }).then(() => location.reload());
            },
            error: function(err) {
                let msg = err.responseJSON?.message ?? 'Something went wrong!';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });

    // === EDIT ===
    $(document).on('click', '.btnEdit', function() {
        const id = $(this).data('id');
        let editUrl = "{{ route('departments.edit', ':id') }}".replace(':id', id);

        $.get(editUrl, function(data) {
            $('#dep_id').val(data.id);
            $('#name').val(data.name);
            $('#code').val(data.code);
            $('#description').val(data.description);
            $('#modalTitle').text('Edit Department');
            modal.show();
        });
    });

    // === DELETE ===
    $(document).on('click', '.btnDelete', function() {
        const id = $(this).data('id');
        let deleteUrl = "{{ route('departments.destroy', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: res.message,
                            showConfirmButton: false,
                            timer: 1200
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to delete this department.'
                        });
                    }
                });
            }
        });
    });
});
</script>


@endsection
