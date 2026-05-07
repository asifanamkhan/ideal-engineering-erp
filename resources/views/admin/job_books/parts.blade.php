@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }

    .part-row {
        background-color: #f8f9fc;
        transition: all 0.2s;
    }

    .part-row:hover {
        background-color: #f1f3f9;
    }

    .remove-part {
        cursor: pointer;
        color: #e74a3b;
    }

    .remove-part:hover {
        color: #c0392b;
    }

    .table-calculations {
        background-color: #f8f9fc;
        font-weight: 600;
    }

    .info-card {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        border-left: 3px solid #4e73df;
    }

    .info-label {
        font-size: 11px;
        color: #858796;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .info-value {
        font-size: 13px;
        font-weight: 500;
        color: #2c3e50;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-microchip me-2"></i> Job Purse</h4>
            </div>
            <div>
                <a href="{{ route('admin.job-books.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Jobs
                </a>
            </div>
        </div>
    </div>

    <form id="partsForm">
        @csrf

        <!-- Job Search Section -->
        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-2">
                            <label class="form-label fw-bold">Search Job <span class="text-danger">*</span></label>
                            <select id="job_id" class="form-control" style="width: 100%;" required>
                                <option value="">Search and select job...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Information & Purse Info - Side by Side -->
        <div class="card shadow mt-3" id="jobInfoCard" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Job Information & Purse Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Side: Job Information -->
                    <div class="col-md-6">
                        <h6 class="text-primary mb-2"><i class="fas fa-briefcase me-2"></i> Job Details</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Job ID</div>
                                    <div class="info-value fw-bold" id="displayJobId">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Job Date</div>
                                    <div class="info-value" id="displayJobDate">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Job Status</div>
                                    <div class="info-value" id="displayJobStatus">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Engine</div>
                                    <div class="info-value" id="displayEngine">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Vehicle Registration</div>
                                    <div class="info-value" id="displayVehicleReg">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Customer</div>
                                    <div class="info-value" id="displayCustomer">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Phone</div>
                                    <div class="info-value" id="displayPhone">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Address</div>
                                    <div class="info-value" id="displayAddress">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card">
                                    <div class="info-label">Job Description</div>
                                    <div class="info-value" id="displayDescription">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Purse Information -->
                    <div class="col-md-6">
                        <h6 class="text-primary mb-2"><i class="fas fa-boxes me-2"></i> Purse Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Purse Status</label>
                                    <select name="parts_status" id="parts_status" class="form-control">
                                        <option value="not_received">Not Received</option>
                                        <option value="partial">Partial Received</option>
                                        <option value="received">Received</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Purse Amount</div>
                                    <div class="info-value text-primary fw-bold" id="displayPartsAmount">0.00</div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Purse Description</label>
                                    <textarea name="parts_description" id="parts_description" class="form-control" rows="2" placeholder="Enter parts description..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Parts Selection -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card shadow" id="partsCard" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-microchip me-2"></i> Parts Selection</h6>
                    </div>
                    <div class="card-body">
                        <!-- Add Part Row -->
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Select Part <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select id="part_id" class="form-control" style="width: 100%;">
                                        <option value=""></option>
                                        @foreach($parts as $part)
                                        <option value="{{ $part->id }}" data-name="{{ $part->name }}"
                                            data-brand="{{ $part->brand }}" data-model="{{ $part->model }}"
                                            data-price="{{ $part->price }}">
                                            {{ $part->name }} - {{ $part->brand }} {{ $part->model }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#partModal" style="white-space: nowrap;">
                                        <i class="fas fa-plus-circle me-1"></i> New
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Size <span class="text-danger">*</span></label>
                                <select id="size_id" class="form-control">
                                    @foreach($sizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" id="part_quantity" class="form-control" value="1" min="1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button type="button" id="addPartBtn" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-2"></i> Add Part
                                </button>
                            </div>
                        </div>

                        <!-- Parts Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered" id="partsTable">
                                <thead class="table-head">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="55%">Part Details</th>
                                        <th width="15%">Size</th>
                                        <th width="10%">Qty</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="partsTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No parts added</td
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4 mb-4 text-center">
                        <button type="button" class="btn btn-lg btn-secondary px-4" id="resetBtn">
                            <i class="fas fa-undo me-2"></i> Reset
                        </button>
                        <button type="button" id="savePartsBtn" class="btn btn-lg btn-success px-4">
                            <i class="fas fa-save me-2"></i> Save Purse
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Part Modal -->
<div class="modal fade" id="partModal" tabindex="-1" aria-labelledby="partModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white" id="partModalLabel">
                    <i class="fas fa-microchip"></i> Add New Part
                </h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-1x fa-times"></i>
                </button>
            </div>
            <form id="partForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Brand</label>
                        <input type="text" class="form-control" id="brand" name="brand">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="savePartBtn">Save Part</button>
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
        $('#parts-index-sidebar, #jobs-sidebar').addClass('active');
        $('#collapseJobs').addClass('show');

        let selectedJobId = null;
        let partsList = [];
        let rowCounter = 0;
        let isEditMode = false;
        let editJobId = null;

        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        var urlJobId = getUrlParameter('job_id');

        var pathSegments = window.location.pathname.split('/');
        var lastSegment = pathSegments[pathSegments.length - 1];
        if (lastSegment && !isNaN(lastSegment) && window.location.pathname.includes('/edit/')) {
            isEditMode = true;
            editJobId = lastSegment;
        } else if (urlJobId && urlJobId !== '') {
            isEditMode = true;
            editJobId = urlJobId;
        }

        $('#job_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search by Job ID or Customer Name...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ url('admin/jobs/search') }}",
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
            templateResult: formatJob,
            templateSelection: formatJobSelection
        });

        function formatJob(job) {
            if (job.loading) return job.text;
            if (!job.job_id) return job.text;
            return $('<div><strong>' + job.job_id + '</strong><br><small>' + (job.customer_name || '') + ' | ' + (job.job_date || '') + '</small></div>');
        }

        function formatJobSelection(job) {
            if (!job.job_id) return job.text;
            return job.job_id + ' - ' + (job.customer_name || '');
        }

        $('#part_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select part',
            allowClear: true,
            width: '100%',
        });

        if (isEditMode && editJobId) {
            $('#job_id').prop('disabled', true);
            $('#job_id').parent().find('.select2-container').css('opacity', '0.7');

            $.ajax({
                url: "{{ url('admin/job-parts/get-details') }}/" + editJobId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var jobOption = new Option(
                            response.job.job_id + ' - ' + response.job.customer_name,
                            response.job.id,
                            true,
                            true
                        );
                        $('#job_id').append(jobOption).trigger('change');
                        selectedJobId = response.job.id;
                        loadJobDetails(selectedJobId);
                    }
                }
            });
        } else if (urlJobId && urlJobId !== '') {
            $.ajax({
                url: "{{ url('admin/job-parts/get-details') }}/" + urlJobId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var jobOption = new Option(
                            response.job.job_id + ' - ' + response.job.customer_name,
                            response.job.id,
                            true,
                            true
                        );
                        $('#job_id').empty().append(jobOption).trigger('change');
                        selectedJobId = response.job.id;
                        loadJobDetails(selectedJobId);
                    }
                }
            });
        }

        $('#job_id').on('change', function() {
            if (!isEditMode) {
                selectedJobId = $(this).val();
                if (selectedJobId) {
                    loadJobDetails(selectedJobId);
                } else {
                    $('#jobInfoCard').hide();
                    $('#partsCard').hide();
                }
            }
        });

        function getStatusBadge(status) {
            const badges = {
                'pending': 'badge bg-warning',
                'in_progress': 'badge bg-info',
                'completed': 'badge bg-success',
                'cancelled': 'badge bg-danger'
            };
            const badgeClass = badges[status] || 'badge bg-secondary';
            const statusText = status ? status.replace('_', ' ').toUpperCase() : 'PENDING';
            return '<span class="' + badgeClass + '">' + statusText + '</span>';
        }

        function loadJobDetails(jobId) {
            $.ajax({
                url: "{{ url('admin/job-parts/get-details') }}/" + jobId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#displayJobId').text(response.job.job_id);
                        $('#displayJobStatus').html(getStatusBadge(response.job.job_status));
                        $('#displayJobDate').text(response.job.job_date);
                        $('#displayEngine').text(response.job.engine || 'N/A');
                        $('#displayVehicleReg').text(response.job.vehicle_registration_no || 'N/A');
                        $('#displayDescription').text(response.job.descriptions || 'N/A');
                        $('#displayCustomer').text(response.job.customer_name || 'N/A');
                        $('#displayPhone').text(response.job.customer_phone || 'N/A');
                        $('#displayAddress').text(response.job.customer_address || 'N/A');

                        $('#jobInfoCard').show();
                        $('#partsCard').show();

                        $('#parts_status').val(response.job.parts_status || 'not_received');
                        $('#parts_description').val(response.job.parts_description || '');
                        $('#displayPartsAmount').text(parseFloat(response.job.parts_amount || 0).toFixed(2));

                        if (response.parts && response.parts.length > 0) {
                            partsList = [];
                            rowCounter = 0;
                            response.parts.forEach(function(part) {
                                partsList.push({
                                    temp_id: rowCounter++,
                                    part_id: part.parts_id,
                                    part_name: part.part_name,
                                    part_brand: part.brand || '',
                                    part_model: part.model || '',
                                    size_id: part.size_id,
                                    size_name: part.size_name,
                                    price: parseFloat(part.single_price),
                                    quantity: part.quantity,
                                    total: parseFloat(part.total_price),
                                    db_id: part.id
                                });
                            });
                            renderPartsTable();
                        }
                    }
                }
            });
        }

        $('#addPartBtn').click(function() {
            var partId = $('#part_id').val();
            var partName = $('#part_id option:selected').data('name');
            var partBrand = $('#part_id option:selected').data('brand') || '';
            var partModel = $('#part_id option:selected').data('model') || '';
            var partPrice = parseFloat($('#part_id option:selected').data('price')) || 0;
            var sizeId = $('#size_id').val();
            var sizeName = $('#size_id option:selected').text();
            var quantity = parseInt($('#part_quantity').val());

            if (!partId || !sizeId || !quantity || quantity < 1) {
                Swal.fire('Error', 'Please fill all fields', 'error');
                return;
            }

            var existingIndex = partsList.findIndex(p => p.part_id == partId && p.size_id == sizeId);
            if (existingIndex !== -1) {
                Swal.fire('Warning', 'This part with same size already exists!', 'warning');
                return;
            }

            partsList.push({
                temp_id: rowCounter++,
                part_id: partId,
                part_name: partName,
                part_brand: partBrand,
                part_model: partModel,
                size_id: sizeId,
                size_name: sizeName,
                price: partPrice,
                quantity: quantity
            });

            renderPartsTable();

            $('#part_id').val('').trigger('change');
            $('#part_quantity').val(1);
        });

        function renderPartsTable() {
            var tbody = $('#partsTableBody');
            tbody.empty();

            if (partsList.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted">No parts added</td</tr>');
                return;
            }

            $.each(partsList, function(index, part) {
                var row = `
                    <tr class="part-row">
                        <td class="text-center">${index + 1}</td
                        <td>
                            <strong>${escapeHtml(part.part_name)}</strong><br>
                            <small class="text-muted">${escapeHtml(part.part_brand)} ${escapeHtml(part.part_model)}</small>
                            <input type="hidden" class="part-id" value="${part.part_id}">
                            <input type="hidden" class="size-id" value="${part.size_id}">
                        </td
                        <td>${escapeHtml(part.size_name)}</td
                        <td>
                            <input type="number" class="form-control text-center form-control-sm qty-input" data-index="${index}" value="${part.quantity}" min="1" style="width: 70px;">
                            <input type="hidden" class="price-hidden" value="${part.price}">
                        </td
                        <td class="text-center">
                            <i class="fas fa-trash-alt remove-part text-danger" data-index="${index}" style="cursor: pointer;"></i>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Bind quantity change events
            $('.qty-input').off('change').on('change', function() {
                var index = $(this).data('index');
                var newQty = parseInt($(this).val());
                if (!isNaN(newQty) && newQty >= 1) {
                    partsList[index].quantity = newQty;
                }
            });
        }

        $(document).on('click', '.remove-part', function() {
            var index = $(this).data('index');
            partsList.splice(index, 1);
            renderPartsTable();
        });

        $('#resetBtn').click(function() {
            Swal.fire({
                title: 'Reset Form?',
                text: 'All unsaved data will be lost!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, reset it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!isEditMode) {
                        $('#job_id').val('').trigger('change');
                    }
                    partsList = [];
                    renderPartsTable();
                    if (!isEditMode) {
                        $('#jobInfoCard').hide();
                        $('#partsCard').hide();
                    }
                    Swal.fire('Reset!', 'Form has been reset.', 'success');
                }
            });
        });

        $('#savePartsBtn').click(function() {
            if (!selectedJobId && !isEditMode) {
                Swal.fire('Error', 'Please select a job first', 'error');
                return;
            }
            if (partsList.length === 0) {
                Swal.fire('Error', 'Please add at least one part', 'error');
                return;
            }

            var url = isEditMode ? "{{ url('admin/job-parts/update') }}/" + selectedJobId : "{{ url('admin/job-parts/store') }}";
            var method = isEditMode ? 'PUT' : 'POST';

            var formData = {
                job_id: selectedJobId,
                parts_status: $('#parts_status').val(),
                parts_description: $('#parts_description').val(),
                parts: partsList,
                _token: '{{ csrf_token() }}'
            };

            var saveBtn = $('#savePartsBtn');
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: url,
                type: method,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = "{{ url('admin/job-parts/create') }}?job_id=" + selectedJobId;
                        });
                    } else {
                        Swal.fire('Error!', response.message || 'Failed to save', 'error');
                        saveBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Purse');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to save', 'error');
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Purse');
                }
            });
        });

        $('#partForm').submit(function(e) {
            e.preventDefault();
            var formData = {
                name: $('#name').val(),
                brand: $('#brand').val(),
                model: $('#model').val(),
                status: $('#status').val(),
                description: $('#description').val(),
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: "{{ route('admin.parts.store') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.part) {
                        var newOption = new Option(response.part.name + ' - ' + (response.part.brand || '') + ' ' + (response.part.model || ''), response.part.id, false, false);
                        newOption.setAttribute('data-name', response.part.name);
                        newOption.setAttribute('data-brand', response.part.brand || '');
                        newOption.setAttribute('data-model', response.part.model || '');
                        newOption.setAttribute('data-price', response.part.price || 0);
                        $('#part_id').append(newOption);
                        $('#partModal').modal('hide');
                        $('#partForm')[0].reset();
                        Swal.fire('Success!', 'Part added successfully', 'success');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to add part', 'error');
                }
            });
        });

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
    });
</script>
@endsection
