@extends('layouts.dashboard.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }

    .service-row {
        background-color: #f8f9fc;
        transition: all 0.2s;
    }

    .service-row:hover {
        background-color: #f1f3f9;
    }

    .remove-service {
        cursor: pointer;
        color: #e74a3b;
    }

    .remove-service:hover {
        color: #c0392b;
    }

    .table-calculations {
        background-color: #f8f9fc;
        font-weight: 600;
    }

    .price-input,
    .qty-input {
        background-color: #fff;
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

    .description-item {
        background: #fff;
        border: 1px solid #e3e6f0;
        padding: 8px 12px;
        margin-bottom: 8px;
        border-radius: 6px;
    }

    #vatPercentRow {
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i> Job Invoice</h4>
            </div>
            <div>
                <a href="{{ route('admin.job-books.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Jobs
                </a>
            </div>
        </div>
    </div>

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

    <form id="invoiceForm">
        @csrf

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

        <div class="card shadow mt-3" id="jobInfoCard" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Job Information & Invoice</h6>
            </div>
            <div class="card-body">
                <div class="row">
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
                            <div class="col-md-8">
                                <div class="info-card">
                                    <div class="info-label">Address</div>
                                    <div class="info-value" id="displayAddress">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-2"><i class="fas fa-file-signature me-2"></i> Invoice Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><label class="form-label fw-bold">Invoice Date <span
                                            class="text-danger">*</span></label><input type="date" name="invoice_date"
                                        id="invoice_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><label class="form-label fw-bold">Invoice Status</label><select
                                        name="invoice_status" id="invoice_status" class="form-control">
                                        <option value="unpaid">Unpaid</option>
                                        <option value="paid">Paid</option>
                                        <option value="partial">Partial</option>
                                    </select></div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2"><label class="form-label fw-bold">Invoice Description</label><textarea
                                        name="invoice_description" id="invoice_description" class="form-control"
                                        rows="2" placeholder="Enter invoice description..."></textarea></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card shadow" id="jobDescriptionsCard" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i> Job Descriptions</h6>
                    </div>
                    <div class="card-body">
                        <div id="jobDescriptionsList">
                            <p class="text-muted text-center">Select a job to view descriptions</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card shadow" id="servicesCard" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-tools me-2"></i> Services Selection</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2 mb-2">
                            <button type="button" id="printInvoiceBtn" class="btn btn-sm btn-info"
                                style="display: none;"><i class="fas fa-print me-1"></i> Print Invoice</button>
                        </div>

                        <div class="row mb-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Service <span
                                        class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select id="service_id" class="form-control" style="width: 100%;">
                                        <option value=""></option>
                                        @foreach($services as $service)
                                        <option value="{{ $service->id }}" data-name="{{ $service->name }}"
                                            data-price="{{ $service->price }}">{{ $service->name }} - ({{
                                            number_format($service->price, 2) }})</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#serviceModal" style="white-space: nowrap;"><i
                                            class="fas fa-plus-circle me-1"></i> New</button>
                                </div>
                            </div>
                            <div class="col-md-2"><label class="form-label fw-bold">Unit <span
                                        class="text-danger">*</span></label><select id="service_unit"
                                    class="form-control">
                                    <option value="">Select Unit</option>@foreach($units as $unit)<option @if ($unit->
                                        is_default == 1) selected @endif value="{{ $unit->id }}" data-name="{{
                                        $unit->name }}">{{ $unit->name }}</option>@endforeach
                                </select></div>
                            <div class="col-md-2"><label class="form-label fw-bold">Quantity <span
                                        class="text-danger">*</span></label><input type="number" id="service_quantity"
                                    class="form-control" value="1" min="1"></div>
                            <div class="col-md-2"><label class="form-label fw-bold">&nbsp;</label><button type="button"
                                    id="addServiceBtn" class="btn btn-success w-100"><i class="fas fa-plus me-2"></i>
                                    Add</button></div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="35%">Service Details</th>
                                        <th width="10%">Unit</th>
                                        <th width="12%">Unit Price</th>
                                        <th width="8%">Qty</th>
                                        <th width="12%">Total</th>
                                        <th width="8%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="servicesTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No services added</td< /tr>
                                </tbody>
                                <tfoot class="table-calculations">
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                        <td class="fw-bold text-end" id="subtotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr id="transportRow">
                                        <td colspan="5" class="text-end fw-bold">Transport Cost:</td>
                                        <td class="fw-bold text-end" id="transportCostDisplay">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr id="vatRow" style="display: none;">
                                        <td colspan="5" class="text-end fw-bold">VAT (<span
                                                id="vatPercentage">0</span>%):</td>
                                        <td class="fw-bold text-end" id="vatAmount">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-end fw-bold">Total:</td>
                                        <td class="fw-bold text-end" id="totalAmount">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3 offset-md-9">
                                <div class="mb-2"><label class="form-label fw-bold">Transport Cost</label><input
                                        type="number" step="0.01" name="invoice_transport_cost"
                                        id="invoice_transport_cost" class="form-control" value="0"
                                        placeholder="Enter transport cost"></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3 offset-md-9">
                                <div class="mb-2"><label class="form-label fw-bold">Discount</label><input type="number"
                                        step="0.01" name="invoice_discount" id="invoice_discount" class="form-control"
                                        value="0" placeholder="Enter discount amount"></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3 offset-md-9">
                                <div class="mb-2"><label class="form-label fw-bold">VAT Type</label><select
                                        name="invoice_vat_type" id="invoice_vat_type" class="form-control">
                                        <option value="include">Include VAT</option>
                                        <option value="exclude">Exclude VAT</option>
                                    </select></div>
                            </div>
                        </div>
                        <div class="row" id="vatPercentRow">
                            <div class="col-md-3 offset-md-9">
                                <div class="mb-2"><label class="form-label fw-bold">VAT (%)</label><input type="number"
                                        name="invoice_vat" id="invoice_vat" class="form-control" step="0.01" value="0"
                                        placeholder="Enter VAT percentage"></div>
                            </div>
                        </div>

                        <div id="paymentSectionContainer" class="mt-3">
                            @include('admin.partials.payments.payment-section', ['inline' => true])
                        </div>
                    </div>
                    <div class="mt-4 mb-4 text-center">
                        <button type="button" class="btn btn-lg btn-secondary px-4" id="resetBtn"><i
                                class="fas fa-undo me-2"></i> Reset</button>
                        <button type="button" id="saveInvoiceBtn" class="btn btn-lg btn-success px-4"><i
                                class="fas fa-save me-2"></i> Save Invoice</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-tools"></i> Add New Service</h5><button
                    type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                        class="fa fa-1x fa-times"></i></button>
            </div>
            <form id="serviceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-2"><label for="service_name">Name <span class="text-danger">*</span></label><input
                            type="text" class="form-control" id="service_name" name="name" required></div>
                    <div class="mb-2"><label for="service_price">Price <span class="text-danger">*</span></label><input
                            type="number" step="0.01" class="form-control" id="service_price" name="price" required>
                    </div>
                    <div class="mb-2"><label for="service_description">Description</label><textarea class="form-control"
                            id="service_description" name="description" rows="3"></textarea></div>
                    <div class="mb-2"><label for="service_status">Status</label><select class="form-control"
                            id="service_status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"
                        id="saveServiceBtn">Save Service</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('admin.partials.payments.payment-script')

<script>
    $('#invoices-index-sidebar, #jobs-sidebar').addClass('active');
$('#collapseJobs').addClass('show');

$(document).ready(function() {
    let selectedJobId = null, selectedCustomerId = null, servicesList = [], rowCounter = 0;

    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    var urlJobId = getUrlParameter('job_id');

    $('#job_id').select2({
        theme: 'bootstrap-5', placeholder: 'Search by Job ID or Customer Name...', allowClear: true, width: '100%',
        ajax: {
            url: "{{ url('admin/jobs/search') }}", type: 'GET', dataType: 'json', delay: 250,
            data: function(params) { return { search: params.term, page: params.page || 1 }; },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 1,
        templateResult: function(job) { return job.loading ? job.text : (job.job_id ? $('<div><strong>' + job.job_id + '</strong><br><small>' + (job.customer_name || '') + ' | ' + (job.job_date || '') + '</small></div>') : job.text); },
        templateSelection: function(job) { return job.job_id ? job.job_id + ' - ' + (job.customer_name || '') : job.text; }
    });

    $('#service_id').select2({ theme: 'bootstrap-5', placeholder: 'Search and select service', allowClear: true, width: '100%' });

    if (urlJobId && urlJobId !== '') {
        $.ajax({
            url: "{{ url('admin/jobs/get-invoice-details') }}/" + urlJobId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var jobOption = new Option(response.job.job_id + ' - ' + response.job.customer_name, response.job.id, true, true);
                    $('#job_id').empty().append(jobOption).trigger('change');
                    selectedJobId = response.job.id;
                    selectedCustomerId = response.job.customer_id;
                    loadJobDetails(selectedJobId);
                }
            }
        });
    }

    $('#job_id').on('change', function() {
        selectedJobId = $(this).val();
        if (selectedJobId) loadJobDetails(selectedJobId);
        else { $('#jobInfoCard, #jobDescriptionsCard, #servicesCard, #paymentSectionContainer').hide(); }
    });

    function getStatusBadge(status) {
        const badges = { 'pending': 'badge bg-warning', 'in_progress': 'badge bg-info', 'completed': 'badge bg-success', 'cancelled': 'badge bg-danger', 'not_started': 'badge bg-secondary' };
        const statusKey = (status || 'pending').toLowerCase();
        const badgeClass = badges[statusKey] || 'badge bg-secondary';
        let statusText = statusKey.replace('_', ' ').replace('-', ' ').toUpperCase();
        return '<span class="' + badgeClass + '">' + statusText + '</span>';
    }

    function loadJobDescriptions(jobId) {
        $.ajax({
            url: "{{ url('admin/job-books/get-descriptions') }}/" + jobId,
            type: 'GET',
            success: function(response) {
                if (response.success && response.descriptions && response.descriptions.length > 0) {
                    var html = '';
                    $.each(response.descriptions, function(index, desc) {
                        html += '<div class="description-item"><strong>' + (index + 1) + '.</strong> ' + escapeHtml(desc.description || '') + '</div>';
                    });
                    $('#jobDescriptionsList').html(html);
                } else {
                    $('#jobDescriptionsList').html('<p class="text-muted text-center">No job descriptions available</p>');
                }
            }
        });
    }

    function loadJobDetails(jobId) {
        $.ajax({
            url: "{{ url('admin/jobs/get-invoice-details') }}/" + jobId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    selectedCustomerId = response.job.customer_id;
                    $('#displayJobId').text(response.job.job_id);
                    $('#displayJobStatus').html(getStatusBadge(response.job.job_status));
                    $('#displayJobDate').text(response.job.job_date);
                    $('#displayEngine').text(response.job.engine || 'N/A');
                    $('#displayVehicleReg').text(response.job.vehicle_registration_no || 'N/A');
                    $('#displayCustomer').text(response.job.customer_name || 'N/A');
                    $('#displayPhone').text(response.job.customer_phone || 'N/A');
                    $('#displayAddress').text(response.job.customer_address || 'N/A');
                    loadJobDescriptions(jobId);
                    $('#jobInfoCard, #jobDescriptionsCard, #servicesCard, #paymentSectionContainer').show();

                    if (response.job.invoice_date) $('#printInvoiceBtn').show();
                    else $('#printInvoiceBtn').hide();

                    if (response.job.invoice_date || response.job.invoice_description || (response.services && response.services.length > 0)) {
                        $('#invoice_date').val(response.job.invoice_date || '{{ date("Y-m-d") }}');
                        $('#invoice_discount').val(response.job.invoice_discount || 0);
                        $('#invoice_transport_cost').val(response.job.invoice_transport_cost || 0);
                        $('#invoice_vat').val(response.job.invoice_vat || 0);
                        $('#invoice_vat_type').val(response.job.invoice_vat_type || 'include');
                        $('#invoice_status').val(response.job.invoice_status || 'unpaid');
                        $('#invoice_description').val(response.job.invoice_description || '');

                        if (response.job.invoice_vat_type === 'include') $('#vatPercentRow').hide();
                        else $('#vatPercentRow').show();

                        if (response.services && response.services.length > 0) {
                            servicesList = []; rowCounter = 0;
                            response.services.forEach(function(service) {
                                servicesList.push({ temp_id: rowCounter++, service_id: service.service_id, service_name: service.service_name, unit_id: service.unit_id || null, unit_name: service.unit_name || '', price: parseFloat(service.price), quantity: service.quantity, total: parseFloat(service.total_price), db_id: service.id });
                            });
                            renderServicesTable();
                            calculateTotals();
                        }
                        setTimeout(function() { showPaymentSection(selectedJobId, selectedCustomerId); }, 50);
                    } else {
                        $('#invoice_date').val('{{ date("Y-m-d") }}');
                        $('#invoice_discount, #invoice_vat, #invoice_transport_cost').val(0);
                        $('#invoice_status').val('unpaid');
                        $('#invoice_description').val('');
                        servicesList = [];
                        renderServicesTable();
                        calculateTotals();
                        showPaymentSection(selectedJobId, selectedCustomerId);
                    }
                }
            }
        });
    }

    $('#addServiceBtn').click(function() {
        var serviceId = $('#service_id').val();
        var serviceName = $('#service_id option:selected').data('name');
        var servicePrice = parseFloat($('#service_id option:selected').data('price'));
        var unitId = $('#service_unit').val();
        var unitName = $('#service_unit option:selected').data('name');
        var quantity = parseInt($('#service_quantity').val());

        if (!serviceId || !unitId || !quantity || quantity < 1) { Swal.fire('Error', 'Please fill all fields', 'error'); return; }
        if (servicesList.findIndex(s => s.service_id == serviceId) !== -1) { Swal.fire('Warning', 'This service already exists!', 'warning'); return; }

        servicesList.push({ temp_id: rowCounter++, service_id: serviceId, service_name: serviceName, unit_id: unitId, unit_name: unitName, price: servicePrice, quantity: quantity, total: servicePrice * quantity });
        renderServicesTable();
        calculateTotals();
        $('#service_id').val('').trigger('change');
        $('#service_quantity').val(1);
    });

    function renderServicesTable() {
        var tbody = $('#servicesTableBody');
        tbody.empty();
        if (servicesList.length === 0) { tbody.append('<tr><td colspan="7" class="text-center text-muted">No services added</td></tr>'); return; }
        $.each(servicesList, function(index, service) {
            var row = '<tr class="service-row"><td class="text-center">' + (index + 1) + '</td><td><strong>' + service.service_name + '</strong><input type="hidden" name="services[' + index + '][id]" value="' + (service.db_id || '') + '"><input type="hidden" name="services[' + index + '][service_id]" value="' + service.service_id + '"></td><td class="text-center">' + service.unit_name + '<input type="hidden" name="services[' + index + '][unit_id]" value="' + service.unit_id + '"></td><td class="text-end"><input type="number" step="0.01" class="form-control form-control-sm price-input" data-index="' + index + '" value="' + service.price.toFixed(2) + '" style="width: 100px;"></td><td class="text-center"><input type="number" class="form-control text-center form-control-sm qty-input" data-index="' + index + '" value="' + service.quantity + '" min="1" style="width: 70px;"></td><td class="total-price-' + index + ' text-end">' + service.total.toFixed(2) + '</td><td class="text-center"><i class="fas fa-trash-alt remove-service text-danger" data-index="' + index + '" style="cursor: pointer;"></i></td></tr>';
            tbody.append(row);
        });
    }

    $(document).on('change input', '.price-input', function() {
        var index = $(this).data('index');
        var newPrice = parseFloat($(this).val());
        if (!isNaN(newPrice) && newPrice >= 0) {
            servicesList[index].price = newPrice;
            servicesList[index].total = servicesList[index].price * servicesList[index].quantity;
            $('.total-price-' + index).text(servicesList[index].total.toFixed(2));
            calculateTotals();
        }
    });

    $(document).on('change input', '.qty-input', function() {
        var index = $(this).data('index');
        var newQty = parseInt($(this).val());
        if (!isNaN(newQty) && newQty >= 1) {
            servicesList[index].quantity = newQty;
            servicesList[index].total = servicesList[index].price * servicesList[index].quantity;
            $('.total-price-' + index).text(servicesList[index].total.toFixed(2));
            calculateTotals();
        }
    });

    $(document).on('click', '.remove-service', function() {
        var index = $(this).data('index');
        servicesList.splice(index, 1);
        renderServicesTable();
        calculateTotals();
    });

    function calculateTotals() {
        var subtotal = 0;
        $.each(servicesList, function(index, service) { subtotal += service.total; });

        var discount = parseFloat($('#invoice_discount').val()) || 0;
        var transportCost = parseFloat($('#invoice_transport_cost').val()) || 0;
        var afterDiscount = subtotal - discount;
        var afterTransport = afterDiscount + transportCost;

        var vatPercent = parseFloat($('#invoice_vat').val()) || 0;
        var vatType = $('#invoice_vat_type').val();
        var vatAmount = 0;
        var totalAmount = afterTransport;

        if (vatType === 'exclude' && vatPercent > 0) {
            vatAmount = (afterTransport * vatPercent) / 100;
            totalAmount = afterTransport + vatAmount;
            $('#vatRow').show();
            $('#vatPercentage').text(vatPercent);
            $('#vatAmount').text(vatAmount.toFixed(2));
        } else {
            $('#vatRow').hide();
        }

        $('#subtotal').text(subtotal.toFixed(2));
        $('#transportCostDisplay').text(transportCost.toFixed(2));
        $('#totalAmount').text(totalAmount.toFixed(2));

        if (selectedJobId && selectedCustomerId) {
            $('#total_amount').text(totalAmount.toFixed(2));
            $('#due_amount').text(totalAmount.toFixed(2));
            $('#due_amount').data('raw', totalAmount);
            $('#max_due').text(totalAmount.toFixed(2));
            $('#payment_amount').attr('max', totalAmount);
        }
    }

    $(document).on('change input', '#invoice_transport_cost, #invoice_discount, #invoice_vat', function() { calculateTotals(); });

    $(document).on('change', '#invoice_vat_type', function() {
        if ($(this).val() === 'include') { $('#vatPercentRow').hide(); $('#invoice_vat').val(0); }
        else { $('#vatPercentRow').show(); }
        calculateTotals();
    });

    if ($('#invoice_vat_type').val() === 'include') { $('#vatPercentRow').hide(); $('#invoice_vat').val(0); }
    else { $('#vatPercentRow').show(); }

    $('#resetBtn').click(function() {
        Swal.fire({ title: 'Reset Form?', text: 'All unsaved data will be lost!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, reset it!' }).then((result) => {
            if (result.isConfirmed) { $('#job_id').val('').trigger('change'); servicesList = []; renderServicesTable(); calculateTotals(); Swal.fire('Reset!', 'Form has been reset.', 'success'); }
        });
    });

    $('#saveInvoiceBtn').click(function() {
        if (!selectedJobId) { Swal.fire('Error', 'Please select a job first', 'error'); return; }
        if (servicesList.length === 0) { Swal.fire('Error', 'Please add at least one service', 'error'); return; }

        var subtotal = 0;
        $.each(servicesList, function(index, service) { subtotal += service.total; });
        var discount = parseFloat($('#invoice_discount').val()) || 0;
        var transportCost = parseFloat($('#invoice_transport_cost').val()) || 0;
        var vatPercent = parseFloat($('#invoice_vat').val()) || 0;
        var vatType = $('#invoice_vat_type').val();
        var afterDiscount = subtotal - discount;
        var afterTransport = afterDiscount + transportCost;
        var vatAmount = 0;
        var totalAmount = afterTransport;
        if (vatType === 'exclude' && vatPercent > 0) {
            vatAmount = (afterTransport * vatPercent) / 100;
            totalAmount = afterTransport + vatAmount;
        }

        var paymentAmount = parseFloat($('#payment_amount').val());
        var paymentModeId = $('#payment_mode_id').val();
        var narration = $('#narration').val();

        var paymentData = {
            chq_no: $('input[name="chq_no"]').val(),
            chq_date: $('input[name="chq_date"]').val(),
            card_no: $('input[name="card_no"]').val(),
            online_trx_id: $('input[name="online_trx_id"]').val(),
            online_trx_dt: $('input[name="online_trx_dt"]').val(),
            mfs_name: $('select[name="mfs_name"]').val(),
            bank_code: $('select[name="bank_code"]').val(),
            bank_ac_no: $('input[name="bank_ac_no"]').val()
        };

        var formData = {
            job_id: selectedJobId,
            invoice_date: $('#invoice_date').val(),
            invoice_discount: discount,
            invoice_transport_cost: transportCost,
            invoice_vat: vatType === 'include' ? 0 : vatPercent,
            invoice_vat_type: vatType,
            invoice_vat_amount: vatAmount,
            invoice_amount: totalAmount,
            invoice_status: $('#invoice_status').val(),
            invoice_description: $('#invoice_description').val(),
            services: servicesList,
            payment_amount: paymentAmount,
            payment_mode_id: paymentModeId,
            narration: narration,
            payment_data: paymentData,
            _token: '{{ csrf_token() }}'
        };

        if (paymentAmount && paymentAmount > 0) {
            if (!paymentModeId) { Swal.fire('Error', 'Please select payment mode', 'error'); return; }
            var dueAmount = $('#due_amount').data('raw');
            if (paymentAmount > dueAmount) { Swal.fire('Error', 'Payment amount cannot exceed due amount', 'error'); return; }
        }

        var saveBtn = $('#saveInvoiceBtn');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: "{{ url('admin/job-invoices/store') }}", type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false }).then(function() {
                        window.location.href = "{{ url('admin/job-invoices/create') }}?job_id=" + selectedJobId;
                    });
                } else { Swal.fire('Error!', response.message || 'Failed to save', 'error'); saveBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Invoice'); }
            },
            error: function(xhr) { Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to save', 'error'); saveBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Invoice'); }
        });
    });

    $('#serviceForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('admin.services.store') }}", type: 'POST',
            data: { name: $('#service_name').val(), price: $('#service_price').val(), description: $('#service_description').val(), status: $('#service_status').val(), _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success && response.service) {
                    var newOption = new Option(response.service.name + ' - ' + response.service.price, response.service.id, false, false);
                    newOption.setAttribute('data-name', response.service.name);
                    newOption.setAttribute('data-price', response.service.price);
                    $('#service_id').append(newOption);
                    $('#serviceModal').modal('hide');
                    $('#serviceForm')[0].reset();
                    Swal.fire('Success!', 'Service added successfully', 'success');
                }
            }
        });
    });

    function showPaymentSection(jobId, customerId) {
        $('#paymentSectionContainer').show();
        PaymentHandler.init({
            paymentFor: 'customer', paymentForId: customerId, type: 'job', typeId: jobId,
            getDetailsUrl: "{{ url('admin/job-books/get-payment-details') }}/" + jobId,
            processUrl: "{{ url('admin/job-books/process-payment') }}", updateUrl: "{{ url('admin/job-books/update-payment') }}",
            getHistoryUrl: "{{ url('admin/job-books/get-payment-history') }}", getPaymentUrl: "{{ url('admin/job-books/get-payment') }}",
            deleteUrl: "{{ url('admin/job-books/delete-payment') }}", modalTitle: 'Payment Management', inline: true
        });
    }

    $('#printInvoiceBtn').click(function() {
        if (!selectedJobId) return;
        Swal.fire({ title: 'Preparing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({
            url: "{{ url('admin/job-books/print') }}", type: 'POST',
            data: { _token: "{{ csrf_token() }}", job_id: selectedJobId, documents: ['invoice'] },
            success: function(response) { Swal.close(); if (response.success && response.html) { var printTab = window.open(); printTab.document.write(response.html); printTab.document.close(); } }
        });
    });

    function escapeHtml(str) { if (!str) return ''; return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});
</script>
@endsection
