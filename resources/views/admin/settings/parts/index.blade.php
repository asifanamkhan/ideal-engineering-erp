@extends('layouts.dashboard.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<style>

</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-asterisk"></i> All Parts List</h4>
            </div>

            <div>
                <a href="#" class="btn btn-primary btn shadow-sm px-5" id="addNewPart">
                    <i class="fas fa-plus "></i> Add New Parts
                </a>
            </div>
        </div>
    </div>


    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="parts-table" width="100%" cellspacing="0">
                    <thead class="table-head">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="partModal" tabindex="-1" aria-labelledby="partModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partModalLabel">Add New Part</h5>
                {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
            </div>
            <form id="partForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>

                    <input type="hidden" id="part_id" name="part_id">

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Parts</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#settings-sidebar').addClass('active');
    $('#parts-index-sidebar').addClass('active');
    $('#collapseSettings').addClass('show');

    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#parts-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.parts.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
                { data: 'name', name: 'name', width: '30%' },
                { data: 'price', name: 'price', width: '10%', className: 'text-center' },
                { data: 'status_badge', name: 'status', width: '10%', orderable: false, searchable: false, className: 'text-center' },
                { data: 'description', name: 'description', width: '30%' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '15%', className: 'text-center' }
            ],
            order: [[1, 'asc']],
            dom: '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search parts...",
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "No parts found",
                info: "Showing _START_ to _END_ of _TOTAL_ parts",
                infoEmpty: "No parts available",
                infoFiltered: "(filtered from _MAX_ total parts)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Add New Part button click
        $('#addNewPart').click(function() {
            resetForm();
            $('#partModalLabel').text('Add New Part');
            $('#partForm').attr('action', '{{ route("admin.parts.store") }}');
            // Remove any existing method field
            $('#methodField').remove();
            $('#partModal').modal('show');
        });

        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            resetForm();

            // Show loading in SweetAlert
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ url('admin/parts') }}/" + id + "/edit",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();

                    $('#partModalLabel').text('Edit Part');
                    $('#partForm').attr('action', '{{ url("admin/parts") }}/' + id);

                    // Add hidden method field for PUT request
                    if ($('#methodField').length === 0) {
                        $('#partForm').append('<input type="hidden" name="_method" id="methodField" value="PUT">');
                    } else {
                        $('#methodField').val('PUT');
                    }

                    $('#part_id').val(data.id);
                    $('#name').val(data.name);
                    $('#price').val(data.price);

                    // Fix for status dropdown - convert 1/2 to select option
                    if (data.status == 1) {
                        $('#status').val('1');
                    } else if (data.status == 2) {
                        $('#status').val('0');
                    } else {
                        $('#status').val(data.status);
                    }

                    $('#description').val(data.description);
                    $('#partModal').modal('show');
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load part data',
                        confirmButtonColor: '#4e73df'
                    });
                }
            });
        });

        // Form submission
        $('#partForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = form.serialize();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            $('#formErrors').addClass('d-none').empty();

            // Show loading in SweetAlert
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.close();
                    $('#partModal').modal('hide');
                    table.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#4e73df',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.close();

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<ul class="mb-0">';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                            $('#' + key).addClass('is-invalid');
                            $('#' + key).siblings('.invalid-feedback').text(value[0]);
                        });
                        errorHtml += '</ul>';
                        // $('#formErrors').removeClass('d-none').html(errorHtml);

                        // Scroll to error
                        $('html, body').animate({
                            scrollTop: $('#formErrors').offset().top - 100
                        }, 500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Something went wrong!',
                            confirmButtonColor: '#4e73df'
                        });
                    }
                }
            });
        });

        // Delete button click with SweetAlert
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ url('admin/parts') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        }
                    }).then(response => {
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage('Request failed: ' + (error.responseJSON?.error || 'Something went wrong'));
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: result.value.message,
                        confirmButtonColor: '#4e73df',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });

        // Reset form when modal is hidden
        $('#partModal').on('hidden.bs.modal', function() {
            resetForm();
            $('#methodField').remove();
        });

        // Reset form function
        function resetForm() {
            $('#partForm')[0].reset();
            $('#part_id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            $('#formErrors').addClass('d-none').empty();
        }
    });
</script>
@endsection
