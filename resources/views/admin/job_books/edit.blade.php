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

    .calculation-row td {
        padding: 10px;
        border-top: 2px solid #dee2e6;
    }

    .price-input,
    .qty-input {
        background-color: #fff;
    }

    .price-input:focus,
    .qty-input:focus {
        background-color: #fff;
    }

    .compact-table td,
    .compact-table th {
        padding: 0.5rem;
        vertical-align: middle;
    }
</style>

@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-edit me-2"></i> Edit Job</h4>
            </div>
            <div>
                <a href="{{ route('admin.job-books.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>
    @include('admin.partials.bootstrap-alert')
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

    <form action="{{ route('admin.job-books.update', $jobBook->id) }}" method="POST" enctype="multipart/form-data" id="jobForm">
        @csrf
        @method('PUT')

        <!-- Main Card -->
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> Job Information</h5>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Customer Field with Plus Button Beside -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <select name="customer_id" id="customer_id" class="form-control" style="flex: 1;" required>
                                    <option value="">Search and select customer...</option>
                                </select>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#customerModal" style="white-space: nowrap;">
                                    <i class="fas fa-plus-circle me-1"></i> New
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <!-- Job Date -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Date <span class="text-danger">*</span></label>
                            <input type="date" name="job_date" class="form-control" value="{{ $jobBook->job_date }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <!-- Delivery Date -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ $jobBook->delivery_date }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Engine -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Engine</label>
                            <input type="text" name="engine" class="form-control" value="{{ $jobBook->engine }}" placeholder="Enter engine details">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Status</label>
                            <select name="job_status" class="form-control">
                                <option value="pending" {{ $jobBook->job_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $jobBook->job_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $jobBook->job_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $jobBook->job_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Documents</label>
                            <input type="file" name="documents" class="form-control">
                            @if($jobBook->documents)
                                <small class="text-muted">Current: <a href="{{ asset($jobBook->documents) }}" target="_blank">View Document</a></small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <!-- Assign To (Multiple) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assign To</label>
                            <select name="assign_to[]" id="assign_to" class="form-control" multiple>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ in_array($employee->id, $assignedEmployees) ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">You can select multiple employees</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descriptions</label>
                            <textarea name="descriptions" class="form-control" rows="4" placeholder="Enter job descriptions...">{{ $jobBook->descriptions }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parts Selection Section -->
        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-microchip me-2"></i> Parts Selection</h6>
            </div>
            <div class="card-body">
                <!-- Add Part Row -->
                <div class="row mb-3 align-items-end">
                    <div class="col-md-6">
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
                            <option
                            @if ($size->is_default == 1)
                                selected
                            @endif
                            value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <button type="button" id="addPartBtn" class="btn btn-success w-100">
                            <i class="fas fa-plus me-2"></i> Add Part
                        </button>
                    </div>
                </div>

                <!-- Parts Table -->
                <div class="table-responsive mt-3">
                    <table class="table table-bordered compact-table" id="partsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Part Details</th>
                                <th width="10%">Size</th>
                                <th width="15%">Unit Price</th>
                                <th width="10%">Qty</th>
                                <th width="15%">Total</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="partsTableBody">
                            <!-- Existing parts will be loaded here -->
                        </tbody>
                        <tfoot class="table-calculations">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                <td class="fw-bold" style="text-align: center" id="totalQuantity">0</td>
                                <td class="fw-bold" style="text-align: right" id="totalPrice">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="mt-4 mb-4 text-center">
                <button type="reset" class="btn btn-lg btn-secondary px-4">
                    <i class="fas fa-undo me-2"></i> Reset
                </button>
                <button type="submit" onclick="return confirm('Are you sure you want to update this job?')"
                    class="btn btn-lg btn-success px-4">
                    <i class="fas fa-save me-2"></i> Update Job
                </button>
            </div>
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
            <div class="modal-body">
                @include('admin.contacts.customers.partials.modal-form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
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
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (per unit/size)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price">
                    </div>
                    <div class="mb-3">
                        <label for="brand" class="form-label">Brand name</label>
                        <input type="text" class="form-control" id="brand" name="brand">
                    </div>
                    <div class="mb-3">
                        <label for="model" class="form-label">Model name</label>
                        <input type="text" class="form-control" id="model" name="model">
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
    // Activate sidebar
    $('#jobs-sidebar, #jobs-index-sidebar').addClass('active');
    $('#collapseJobs').addClass('show');

    // Existing customer ID
    var existingCustomerId = "{{ $jobBook->customer_id }}";
    var existingCustomerName = "{{ $jobBook->customer->name ?? '' }}";

    // Initialize Select2 for Customer with AJAX
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

    // Set existing customer if any
    if (existingCustomerId && existingCustomerName) {
        var option = new Option(existingCustomerName, existingCustomerId, true, true);
        $('#customer_id').append(option).trigger('change');
    }

    function formatCustomer(customer) {
        if (customer.loading) return customer.text;
        return $('<div><strong>' + customer.name + '</strong><br><small>' +
            (customer.phone ? '📞 ' + customer.phone + ' | ' : '') +
            (customer.email ? '✉️ ' + customer.email : '') + '</small></div>');
    }

    function formatCustomerSelection(customer) {
        return customer.name || customer.text;
    }

    // Initialize Select2 for Part
    $('#part_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select part',
        allowClear: true,
        width: '100%',
    });

    // Initialize Select2 for Assign To
    $('#assign_to').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select employees',
        allowClear: true,
        width: '100%'
    });

    // Toggle business fields
    $(document).on('change', '#type', function() {
        if ($(this).val() === 'business') {
            $('.business-fields').show();
        } else {
            $('.business-fields').hide();
        }
    });

    // Save Customer
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
                if (response.success && response.customer) {
                    var newOption = new Option(response.customer.name, response.customer.id, true, true);
                    $('#customer_id').append(newOption).trigger('change');
                    $('#customerModal').modal('hide');
                    $('#customerModal form')[0].reset();
                    $('.business-fields').hide();

                    Swal.fire('Success!', 'Customer added successfully', 'success');
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to add customer';
                Swal.fire('Error!', msg, 'error');
            },
            complete: function() {
                saveBtn.prop('disabled', false).html('Save Customer');
            }
        });
    });

    // Save Part
    $('#partForm').submit(function(e) {
        e.preventDefault();

        var partData = {
            name: $('#partForm #name').val(),
            price: $('#partForm #price').val(),
            brand: $('#partForm #brand').val(),
            model: $('#partForm #model').val(),
            status: $('#partForm #status').val(),
            description: $('#partForm #description').val(),
            _token: '{{ csrf_token() }}'
        };

        var saveBtn = $('#savePartBtn');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: "{{ route('admin.parts.store') }}",
            type: 'POST',
            data: partData,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.part) {
                    var optionText = response.part.name + ' - ' + (response.part.brand || '') + ' ' + (response.part.model || '');
                    var newOption = new Option(optionText, response.part.id, false, false);
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
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to add part', 'error');
            },
            complete: function() {
                saveBtn.prop('disabled', false).html('Save Part');
            }
        });
    });

    // Parts array for editing
    let partsList = [];
    let rowCounter = 0;

    // Load existing parts
    @php
        $existingParts = DB::table('job_parts')
            ->where('job_book_id', $jobBook->id)
            ->join('parts', 'job_parts.parts_id', '=', 'parts.id')
            ->join('sizes', 'job_parts.size_id', '=', 'sizes.id')
            ->select('job_parts.*', 'parts.name as part_name', 'parts.brand', 'parts.model', 'sizes.name as size_name')
            ->get();
    @endphp

    var existingParts = @json($existingParts);

    if (existingParts.length > 0) {
        existingParts.forEach(function(part, index) {
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
        calculateTotals();
    }

    // Add Part
    $('#addPartBtn').click(function() {
        var partId = $('#part_id').val();
        var partName = $('#part_id option:selected').data('name');
        var partBrand = $('#part_id option:selected').data('brand');
        var partModel = $('#part_id option:selected').data('model');
        var partPrice = parseFloat($('#part_id option:selected').data('price'));
        var sizeId = $('#size_id').val();
        var sizeName = $('#size_id option:selected').text();

        if (!partId) {
            Swal.fire('Error', 'Please select a part', 'error');
            return;
        }
        if (!sizeId) {
            Swal.fire('Error', 'Please select a size', 'error');
            return;
        }

        var existingIndex = partsList.findIndex(p => p.part_id == partId && p.size_id == sizeId);
        if (existingIndex !== -1) {
            Swal.fire('Warning', 'This part with same size already exists!', 'warning');
            return;
        }

        var partData = {
            temp_id: rowCounter++,
            part_id: partId,
            part_name: partName,
            part_brand: partBrand || '',
            part_model: partModel || '',
            size_id: sizeId,
            size_name: sizeName,
            price: partPrice || 0,
            quantity: 1,
            total: (partPrice || 0) * 1
        };

        partsList.push(partData);
        renderPartsTable();
        calculateTotals();

        $('#part_id').val('').trigger('change');
    });

    // Render Parts Table
    function renderPartsTable() {
        var tbody = $('#partsTableBody');
        tbody.empty();

        if (partsList.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted">No parts added</td></tr>');
            return;
        }

        $.each(partsList, function(index, part) {
            // console.log(part);
            var row = `
                <tr class="part-row">
                    <td>${index + 1}</td>
                    <td>
                        <strong>${part.part_name}</strong><br>
                        <small class="text-muted">${part.part_brand} ${part.part_model}</small>
                        <input type="hidden" name="existing_parts[${index}][id]" value="${part.db_id || ''}">
                        <input type="hidden" name="existing_parts[${index}][part_id]" value="${part.part_id}">
                        <input type="hidden" name="existing_parts[${index}][size_id]" value="${part.size_id}">
                    </td>
                    <td>${part.size_name}</td>
                    <td>
                        <input type="number" step="0.01" class="form-control price-input" data-index="${index}" value="${part.price.toFixed(2)}" style="width: 100px;">
                    </td>
                    <td>
                        <input type="number" class="form-control qty-input" data-index="${index}" value="${part.quantity}" min="1" style="width: 70px;">
                    </td>
                    <td class="total-price-${index}" style="text-align: right;">${part.total.toFixed(2)}</td>
                    <td class="text-center">
                        <i class="fas fa-trash-alt remove-part text-danger" data-index="${index}" style="cursor: pointer;"></i>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Handle Price Change
    $(document).on('change input', '.price-input', function() {
        var index = $(this).data('index');
        var newPrice = parseFloat($(this).val());
        if (!isNaN(newPrice) && newPrice >= 0) {
            partsList[index].price = newPrice;
            partsList[index].total = partsList[index].price * partsList[index].quantity;
            $(`.total-price-${index}`).text(partsList[index].total.toFixed(2));
            calculateTotals();
        }
    });

    // Handle Quantity Change
    $(document).on('change input', '.qty-input', function() {
        var index = $(this).data('index');
        var newQty = parseInt($(this).val());
        if (!isNaN(newQty) && newQty >= 1) {
            partsList[index].quantity = newQty;
            partsList[index].total = partsList[index].price * partsList[index].quantity;
            $(`.total-price-${index}`).text(partsList[index].total.toFixed(2));
            calculateTotals();
        }
    });

    // Remove Part
    $(document).on('click', '.remove-part', function() {
        var index = $(this).data('index');
        partsList.splice(index, 1);
        renderPartsTable();
        calculateTotals();
    });

    // Calculate Totals
    function calculateTotals() {
        var totalQty = 0;
        var totalPrice = 0;

        $.each(partsList, function(index, part) {
            totalQty += part.quantity;
            totalPrice += part.total;
        });

        $('#totalQuantity').text(totalQty);
        $('#totalPrice').text(totalPrice.toFixed(2));
    }

    // Form Submit
    $('#jobForm').submit(function(e) {
        if (partsList.length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Please add at least one part to the job', 'error');
            return false;
        }

        var form = $(this);

        // Remove existing hidden inputs to avoid duplicates
        form.find('input[name^="job_parts"]').remove();
        form.find('input[name^="removed_parts"]').remove();

        // Add current parts data
        partsList.forEach(function(part, idx) {
            if (part.db_id) {
                form.append(`<input type="hidden" name="job_parts[${idx}][id]" value="${part.db_id}">`);
            }
            form.append(`<input type="hidden" name="job_parts[${idx}][part_id]" value="${part.part_id}">`);
            form.append(`<input type="hidden" name="job_parts[${idx}][size_id]" value="${part.size_id}">`);
            form.append(`<input type="hidden" name="job_parts[${idx}][quantity]" value="${part.quantity}">`);
            form.append(`<input type="hidden" name="job_parts[${idx}][single_price]" value="${part.price}">`);
            form.append(`<input type="hidden" name="job_parts[${idx}][total_price]" value="${part.total}">`);
        });
    });
});
</script>
@endsection
