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
        <h1 class="h3 mb-0 text-gray-800">All Candidates List</h1>
        <div>
            <a target="_blank" href="{{ route('candidate.register') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Public Link
            </a>
            <a href="{{ route('candidates.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Candidate
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="candidates-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Applied For</th>
                            <th>Experience</th>
                            <th>Expected salary</th>
                            <th>Status</th>
                            <th>Applied Date</th>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this candidate? This action cannot be undone and all their data will be
                lost.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Candidate Modal -->
<div class="modal fade" id="viewCandidateModal" tabindex="-1" role="dialog" aria-labelledby="viewCandidateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCandidateModalLabel">Candidate Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="candidateDetails">
                <!-- Details will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')

<!-- DataTables -->
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
    $('#candidate-sidebar').addClass('active');
    $('#candidate-index-sidebar').addClass('active');
    $('#collapseCandidate').addClass('show');

    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#candidates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('candidates.index') }}",
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    width: '5%'
                },
                {
                    data: 'name',
                    name: 'name',
                    width: '15%',
                    render: function(data, type, row) {
                        return '<strong>' + data + '</strong>';
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    width: '15%'
                },
                {
                    data: 'phone',
                    name: 'phone',
                    width: '10%',
                    render: function(data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: 'job_id',
                    name: 'job_id',
                    width: '12%',
                    render: function(data) {
                        return data || 'Not Specified';
                    }
                },
                {
                    data: 'total_experience',
                    name: 'total_experience',
                    width: '8%',
                    className: 'text-center',
                    render: function(data) {
                        return data ? data + ' years' : 'N/A';
                    }
                },
                {
                    data: 'expected_salary',
                    name: 'expected_salary',
                    width: '12%',
                    render: function(data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    width: '8%',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'created_at_formatted',
                    name: 'created_at',
                    width: '10%'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: '15%',
                    className: 'text-center'
                }
            ],
            order: [[8, 'desc']], // Order by created_at descending
            dom: '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-secondary btn-sm',
                    text: '<i class="fas fa-copy"></i> Copy'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-secondary btn-sm',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'excel',
                    className: 'btn btn-secondary btn-sm',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                extend: 'pdf',
                className: 'btn btn-secondary btn-sm',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'RAPID - Candidates List',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                    stripHtml: true // This alone should remove the HTML tags
                },
                customize: function (doc) {
                    // Set table widths
                    doc.content[1].table.widths =
                        ['5%', '16%', '16%', '10%', '13%', '8%', '12%', '8%', '12%'];

                    // Force header background color
                    doc.content[1].table.body[0].forEach(function(cell) {
                        cell.fillColor = '#28ACE2';
                        cell.color = 'white';
                        cell.bold = true;
                        cell.alignment = 'center';
                    });

                    // Add footer
                    doc.footer = function (currentPage, pageCount) {
                        return {
                            text: 'Page ' + currentPage + ' of ' + pageCount,
                            alignment: 'center',
                            margin: [0, 10, 0, 0],
                            fontSize: 8
                        };
                    };
                }
            },

            ],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search candidates...",
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "No candidates found",
                info: "Showing _START_ to _END_ of _TOTAL_ candidates",
                infoEmpty: "No candidates available",
                infoFiltered: "(filtered from _MAX_ total candidates)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        var deleteId;
        $(document).on('click', '.delete-btn', function() {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').click(function() {
            $.ajax({
                url: "{{ url('candidates') }}/" + deleteId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    table.draw(); // Reload datatable
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Candidate has been deleted successfully.',
                        confirmButtonColor: '#28ACE2'
                    });
                },
                error: function(xhr) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deleting the candidate.',
                        confirmButtonColor: '#28ACE2'
                    });
                }
            });
        });


    });

    function approveCandidate(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to approve this candidate!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28ACE2',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('candidates.approve', ':id') }}".replace(':id', id),
                type: 'GET',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $('#candidates-table').DataTable().draw(); // Reload datatable
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
                            text: 'Candidate has been approved successfully.',
                            confirmButtonColor: '#28ACE2'
                        });
                    }

                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while approving the candidate.',
                        confirmButtonColor: '#28ACE2'
                    });
                }
            });
        }
    });
}

</script>
@endsection
