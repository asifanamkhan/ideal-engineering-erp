@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }

    .description-row {
        background-color: #f8f9fc;
        transition: all 0.2s;
    }

    .description-row:hover {
        background-color: #f1f3f9;
    }

    .remove-description {
        cursor: pointer;
        color: #e74a3b;
    }

    .remove-description:hover {
        color: #c0392b;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-book me-2"></i> Add New Job</h4>
            </div>
            <div>
                <a href="{{ route('admin.job-books.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Display Messages -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.job-books.store') }}" method="POST" enctype="multipart/form-data" id="jobForm">
        @csrf

        <!-- Main Card -->
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-1"><i class="fas fa-info-circle me-2"></i> Job Information</h5>
                <div class="row">
                    <div class="col-md-12 row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select name="customer_id" id="customer_id" class="form-control" style="flex: 1;"
                                        required>
                                        <option value="">Search and select customer...</option>
                                    </select>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#customerModal" style="white-space: nowrap;">
                                        <i class="fas fa-plus-circle me-1"></i> New
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Job Date <span class="text-danger">*</span></label>
                                <input type="date" name="job_date" class="form-control" value="{{ date('Y-m-d') }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Delivery Date</label>
                                <input type="date" name="delivery_date" class="form-control"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Engine</label>
                                <input type="text" name="engine" class="form-control"
                                    placeholder="Enter engine details">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Vehicle Registration No</label>
                                <input type="text" name="vehicle_registration_no" class="form-control"
                                    placeholder="Enter vehicle registration number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Job Status</label>
                                <select name="job_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Documents</label>
                                <input type="file" name="documents" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Assign To</label>
                                <select name="assign_to[]" id="assign_to" class="form-control" multiple>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">You can select multiple employees</small>
                            </div>
                        </div>
                        {{-- <div class="col-md-12">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Descriptions</label>
                                <textarea name="descriptions" class="form-control" rows="4"
                                    placeholder="Enter job descriptions..."></textarea>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Descriptions Section -->
        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i> Job Descriptions</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Select Job Description</label>
                        <div class="d-flex gap-2">
                            <select id="job_description_id" class="form-control" style="width: 100%;">
                                <option value="">Select Description</option>
                                @foreach($jobDescriptions as $desc)
                                <option value="{{ $desc->id }}" data-description="{{ $desc->description }}">
                                    {{ \Illuminate\Support\Str::limit($desc->description, 100) }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#jobDescriptionModal" style="white-space: nowrap;">
                                <i class="fas fa-plus-circle me-1"></i> New
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <button type="button" id="addDescriptionBtn" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i> Add Description
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered" id="descriptionsTable">
                        <thead class="table-head">
                            <tr>
                                <th width="5%">#</th>
                                <th width="85%">Description</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="descriptionsTableBody">
                            <tr>
                                <td colspan="3" class="text-center text-muted">No descriptions added</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4 text-center">
            <button type="reset" class="btn btn-lg btn-secondary px-4">
                <i class="fas fa-undo me-2"></i> Reset
            </button>
            <button type="submit" class="btn btn-lg btn-success px-4">
                <i class="fas fa-save me-2"></i> Save Job
            </button>
        </div>
    </form>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white" id="customerModalLabel">
                    <i class="fas fa-user-plus"></i> Add New Customer
                </h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-1x fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="customerModalBody">
                @include('admin.contacts.customers.partials.modal-form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Job Description Modal -->
<div class="modal fade" id="jobDescriptionModal" tabindex="-1" aria-labelledby="jobDescriptionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white" id="jobDescriptionModalLabel">
                    <i class="fas fa-plus-circle"></i> Add New Job Description
                </h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-1x fa-times"></i>
                </button>
            </div>
            <form id="jobDescriptionForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="desc_status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            <input type="checkbox" name="is_default" value="1"> Set as Default
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveJobDescriptionBtn">Save Description</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#jobs-sidebar, #jobs-index-sidebar').addClass('active');
        $('#collapseJobs').addClass('show');

        let descriptionItems = [];
        let rowCounter = 0;

        // ========== CUSTOMER SELECT2 (Working) ==========
        $('#customer_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select customer',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('admin.customers.search') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: formatCustomer,
            templateSelection: formatCustomerSelection
        });

        function formatCustomer(customer) {
            if (customer.loading) return customer.text;
            var $container = $(
                '<div><strong>' + customer.name + '</strong><br><small>' +
                (customer.phone ? '📞 ' + customer.phone + ' | ' : '') +
                (customer.email ? '✉️ ' + customer.email : '') + '</small></div>'
            );
            return $container;
        }

        function formatCustomerSelection(customer) {
            return customer.name || customer.text;
        }
        // ===============================================

        // ========== ASSIGN TO SELECT2 ==========
        $('#assign_to').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select employees',
            allowClear: true,
            width: '100%'
        });
        // =======================================

        // ========== JOB DESCRIPTION SELECT2 (Without AJAX, simple) ==========
        $('#job_description_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Description',
            allowClear: true,
            width: '100%'
        });
        // =======================================

        // Auto focus on Select2 search
        $('#customer_id').on('select2:open', function(e) {
            setTimeout(function() {
                var $searchField = $('.select2-container--open .select2-search__field');
                if ($searchField.length) {
                    $searchField[0].focus();
                }
            }, 100);
        });

        // Add Description Button
        $('#addDescriptionBtn').click(function() {
            var descriptionId = $('#job_description_id').val();
            var descriptionText = $('#job_description_id option:selected').data('description');

            if (!descriptionId) {
                Swal.fire('Error', 'Please select a description', 'error');
                return;
            }

            var existingIndex = descriptionItems.findIndex(d => d.description_id == descriptionId);
            if (existingIndex !== -1) {
                Swal.fire('Warning', 'This description already exists!', 'warning');
                return;
            }

            descriptionItems.push({
                temp_id: rowCounter++,
                description_id: descriptionId,
                description_text: descriptionText
            });

            renderDescriptionTable();
            $('#job_description_id').val('').trigger('change');
        });

        function renderDescriptionTable() {
            var tbody = $('#descriptionsTableBody');
            tbody.empty();

            if (descriptionItems.length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center text-muted">No descriptions added</td></tr>');
                return;
            }

            $.each(descriptionItems, function(index, item) {
                var row = `
                    <tr class="description-row">
                        <td>${index + 1}</td>
                        <td>
                            ${item.description_text}
                            <input type="hidden" name="descriptions[${index}][description_id]" value="${item.description_id}">
                        </td>
                        <td class="text-center">
                            <i class="fas fa-trash-alt remove-description text-danger" data-index="${index}" style="cursor: pointer;"></i>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        $(document).on('click', '.remove-description', function() {
            var index = $(this).data('index');
            descriptionItems.splice(index, 1);
            renderDescriptionTable();
        });

        // Save Job Description Modal
$('#jobDescriptionForm').submit(function(e) {
    e.preventDefault();

    var formData = {
        description: $('#description').val(),
        status: $('#desc_status').val(),
        is_default: $('#is_default').is(':checked') ? 1 : 0,
        _token: '{{ csrf_token() }}'
    };

    var saveBtn = $('#saveJobDescriptionBtn');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: "{{ route('admin.job_descriptions.store') }}",
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var newOption = new Option(response.data.description, response.data.id, false, false);
                newOption.setAttribute('data-description', response.data.description);
                $('#job_description_id').append(newOption);

                $('#jobDescriptionModal').modal('hide');
                $('#jobDescriptionForm')[0].reset();
                $('#is_default').prop('checked', false);

                Swal.fire('Success!', 'Job description added successfully', 'success');
            } else {
                Swal.fire('Error!', response.message || 'Failed to add description', 'error');
            }
        },
        error: function(xhr) {
            console.log(xhr);
            var errorMsg = 'Failed to add description';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            Swal.fire('Error!', errorMsg, 'error');
        },
        complete: function() {
            saveBtn.prop('disabled', false).html('Save Description');
        }
    });
});

        // Handle Customer Modal Save
        $('#saveCustomerBtn').click(function() {
            var customerData = {
                name: $('#customerModal #name').val(),
                email: $('#customerModal #email').val(),
                phone: $('#customerModal #phone').val(),
                type: $('#customerModal #type').val(),
                reference: $('#customerModal #reference').val(),
                address: $('#customerModal #address').val(),
                opening_bal: $('#customerModal #opening_bal').val(),
                status: $('#customerModal #status').val(),
                business_name: $('#customerModal #business_name').val(),
                business_phone: $('#customerModal #business_phone').val(),
                tax_no: $('#customerModal #tax_no').val(),
                business_address: $('#customerModal #business_address').val(),
                _token: '{{ csrf_token() }}'
            };

            var saveBtn = $(this);
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.customers.store') }}",
                type: 'POST',
                data: customerData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.customer) {
                            var newOption = new Option(response.customer.name, response.customer.id, true, true);
                            $('#customer_id').append(newOption).trigger('change');
                        }
                        $('#customerModal').modal('hide');
                        if ($('#customerModal form').length) {
                            $('#customerModal form')[0].reset();
                        }
                        $('.business-fields').hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Customer added successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to add customer'
                        });
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Failed to add customer';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                },
                complete: function() {
                    saveBtn.prop('disabled', false).html('Save Customer');
                }
            });
        });
    });
</script>
@endsection
