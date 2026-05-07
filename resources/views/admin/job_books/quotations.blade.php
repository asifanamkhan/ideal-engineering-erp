{{-- resources/views/admin/job_books/quotations.blade.php --}}

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
                <h4 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i> Job Quotation</h4>
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

    <form id="quotationForm">
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
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Job Information & Quotation</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-2"><i class="fas fa-briefcase me-2"></i> Job Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Job ID</div>
                                    <div class="info-value fw-bold" id="displayJobId">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Job Date</div>
                                    <div class="info-value" id="displayJobDate">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Job Status</div>
                                    <div class="info-value" id="displayJobStatus">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Engine</div>
                                    <div class="info-value" id="displayEngine">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Vehicle Registration</div>
                                    <div class="info-value" id="displayVehicleReg">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Customer</div>
                                    <div class="info-value" id="displayCustomer">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Phone</div>
                                    <div class="info-value" id="displayPhone">-</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Address</div>
                                    <div class="info-value" id="displayAddress">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-2"><i class="fas fa-file-signature me-2"></i> Quotation Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><label class="form-label fw-bold">Quotation Date <span
                                            class="text-danger">*</span></label><input type="date" name="quotation_date"
                                        id="quotation_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><label class="form-label fw-bold">Quotation Status</label><select
                                        name="quotation_status" id="quotation_status" class="form-control">
                                        <option value="not_send">Not Send</option>
                                        <option value="send">Send</option>
                                        <option value="pending">Pending</option>
                                    </select></div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2"><label class="form-label fw-bold">Quotation Subject</label><input
                                        type="text" name="quotation_subject" id="quotation_subject" class="form-control"
                                        placeholder="Enter quotation subject..."></div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-2"><label class="form-label fw-bold">Quotation
                                        Description</label><textarea name="quotation_description"
                                        id="quotation_description" class="form-control" rows="2"
                                        placeholder="Enter quotation description..."></textarea></div>
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
                            <button type="button" id="printQuotationBtn" class="btn btn-sm btn-info"
                                style="display: none;"><i class="fas fa-print me-1"></i> Print</button>
                            <button type="button" id="convertToInvoiceBtn" class="btn btn-sm btn-success"
                                style="display: none;"><i class="fas fa-exchange-alt me-1"></i> Convert to
                                Invoice</button>
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
                                        </td>
                                </thead>
                                <tbody id="servicesTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No services added</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-calculations">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                        <td class="fw-bold text-center" id="totalQuantity">0</td>
                                        <td class="fw-bold text-end" id="totalPrice">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr id="vatRow" style="display: none;">
                                        <td colspan="5" class="text-end fw-bold">VAT (<span
                                                id="vatPercentage">0</span>%):</td>
                                        <td class="fw-bold text-end" id="vatAmount">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                        <td class="fw-bold text-end" id="grandTotal">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3 offset-md-9">
                                <div class="mb-2"><label class="form-label fw-bold">VAT Type</label><select
                                        name="quotation_vat_type" id="quotation_vat_type" class="form-control">
                                        <option value="include">Include VAT</option>
                                        <option value="exclude">Exclude VAT</option>
                                    </select></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3 offset-md-9">
                                <div class="mb-2"><label class="form-label fw-bold">VAT (%)</label><input type="number"
                                        name="quotation_vat" id="quotation_vat" class="form-control" step="0.01"
                                        value="0" placeholder="Enter VAT percentage"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 mb-4 text-center">
                        <button type="button" class="btn btn-lg btn-secondary px-4" id="resetBtn"><i
                                class="fas fa-undo me-2"></i> Reset</button>
                        <button type="button" id="saveQuotationBtn" class="btn btn-lg btn-success px-4"><i
                                class="fas fa-save me-2"></i> Save Quotation</button>
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

<script>
    $(document).ready(function() {
    $('#quotations-index-sidebar, #jobs-sidebar').addClass('active');
    $('#collapseJobs').addClass('show');

    let selectedJobId = null;
    let servicesList = [];
    let rowCounter = 0;

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
            url: "{{ url('admin/jobs/get-details') }}/" + urlJobId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var jobOption = new Option(response.job.job_id + ' - ' + response.job.customer_name, response.job.id, true, true);
                    $('#job_id').empty().append(jobOption).trigger('change');
                    selectedJobId = response.job.id;
                    loadJobDetails(selectedJobId);
                }
            }
        });
    }

    $('#job_id').on('change', function() {
        selectedJobId = $(this).val();
        if (selectedJobId) loadJobDetails(selectedJobId);
        else { $('#jobInfoCard, #jobDescriptionsCard, #servicesCard').hide(); }
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
            url: "{{ url('admin/jobs/get-details') }}/" + jobId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#displayJobId').text(response.job.job_id);
                    $('#displayJobStatus').html(getStatusBadge(response.job.job_status));
                    $('#displayJobDate').text(response.job.job_date);
                    $('#displayEngine').text(response.job.engine || 'N/A');
                    $('#displayVehicleReg').text(response.job.vehicle_registration_no || 'N/A');
                    $('#displayCustomer').text(response.job.customer_name || 'N/A');
                    $('#displayPhone').text(response.job.customer_phone || 'N/A');
                    $('#displayAddress').text(response.job.customer_address || 'N/A');
                    loadJobDescriptions(jobId);
                    $('#jobInfoCard, #jobDescriptionsCard, #servicesCard').show();

                    if (response.job.invoice_date) $('#convertToInvoiceBtn').prop('disabled', true);
                    else $('#convertToInvoiceBtn').prop('disabled', false);

                    if (response.job.quotation_date && !response.job.invoice_date && response.services && response.services.length > 0) $('#convertToInvoiceBtn').show();
                    else $('#convertToInvoiceBtn').hide();

                    if (response.job.quotation_date && response.services && response.services.length > 0) $('#printQuotationBtn').show();
                    else $('#printQuotationBtn').hide();

                    if (response.job.quotation_date || response.job.quotation_description || (response.services && response.services.length > 0)) {
                        $('#quotation_date').val(response.job.quotation_date || '{{ date("Y-m-d") }}');
                        $('#quotation_subject').val(response.job.quotation_subject || '');
                        $('#quotation_description').val(response.job.quotation_description || '');
                        $('#quotation_status').val(response.job.quotation_status || 'not_send');
                        $('#quotation_vat').val(response.job.quotation_vat || 0);
                        $('#quotation_vat_type').val(response.job.quotation_vat_type || 'include');

                        if (response.job.quotation_vat_type === 'include') $('#vatPercentRow').hide();
                        else $('#vatPercentRow').show();

                        if (response.services && response.services.length > 0) {
                            servicesList = []; rowCounter = 0;
                            response.services.forEach(function(service) {
                                servicesList.push({ temp_id: rowCounter++, service_id: service.service_id, service_name: service.service_name, unit_id: service.unit_id || null, unit_name: service.unit_name || '', price: parseFloat(service.price), quantity: service.quantity, total: parseFloat(service.total_price), db_id: service.id });
                            });
                            renderServicesTable();
                            calculateTotals();
                        } else {
                            servicesList = [];
                            renderServicesTable();
                            calculateTotals();
                        }
                    } else {
                        $('#quotation_date').val('{{ date("Y-m-d") }}');
                        $('#quotation_subject, #quotation_description').val('');
                        $('#quotation_status').val('not_send');
                        $('#quotation_vat, #quotation_vat_type').val(0);
                        servicesList = [];
                        renderServicesTable();
                        calculateTotals();
                        $('#convertToInvoiceBtn, #printQuotationBtn').hide();
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
        if (servicesList.length === 0) { tbody.append('<td><td colspan="7" class="text-center text-muted">No services added</td></tr>'); return; }
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
        var totalPrice = 0;
        $.each(servicesList, function(index, service) { totalPrice += service.total; });

        var vatPercent = parseFloat($('#quotation_vat').val()) || 0;
        var vatType = $('#quotation_vat_type').val();
        var vatAmount = 0;
        var grandTotal = totalPrice;

        if (vatType === 'exclude' && vatPercent > 0) {
            vatAmount = (totalPrice * vatPercent) / 100;
            grandTotal = totalPrice + vatAmount;
            $('#vatRow').show();
            $('#vatPercentage').text(vatPercent);
            $('#vatAmount').text(vatAmount.toFixed(2));
        } else {
            $('#vatRow').hide();
        }

        $('#totalPrice').text(totalPrice.toFixed(2));
        $('#grandTotal').text(grandTotal.toFixed(2));
        $('#quotation_vat_amount').val(vatAmount);
    }

    $(document).on('change', '#quotation_vat_type', function() {
        if ($(this).val() === 'include') {
            $('#vatPercentRow').hide();  // ← This hides the VAT percentage input
            $('#quotation_vat').val(0);   // ← Set VAT to 0
        } else {
            $('#vatPercentRow').show();   // ← Shows when exclude
        }
        calculateTotals();
    });

    if ($('#quotation_vat_type').val() === 'include') {
        $('#vatPercentRow').hide();  // ← Hide on page load if include
    } else {
        $('#vatPercentRow').show();   // ← Show if exclude
    }
    calculateTotals();

    $(document).on('input', '#quotation_vat', function() { calculateTotals(); });

    $('#resetBtn').click(function() {
        Swal.fire({ title: 'Reset Form?', text: 'All unsaved data will be lost!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, reset it!' }).then((result) => {
            if (result.isConfirmed) { $('#job_id').val('').trigger('change'); servicesList = []; renderServicesTable(); calculateTotals(); Swal.fire('Reset!', 'Form has been reset.', 'success'); }
        });
    });

    $('#saveQuotationBtn').click(function() {
        if (!selectedJobId) { Swal.fire('Error', 'Please select a job first', 'error'); return; }
        if (servicesList.length === 0) { Swal.fire('Error', 'Please add at least one service', 'error'); return; }

        var totalPrice = 0;
        $.each(servicesList, function(index, service) { totalPrice += service.total; });
        var vatPercent = parseFloat($('#quotation_vat').val()) || 0;
        var vatType = $('#quotation_vat_type').val();
        var vatAmount = 0;
        var grandTotal = totalPrice;
        if (vatType === 'exclude' && vatPercent > 0) { vatAmount = (totalPrice * vatPercent) / 100; grandTotal = totalPrice + vatAmount; }

        var formData = {
            job_id: selectedJobId, quotation_date: $('#quotation_date').val(), quotation_subject: $('#quotation_subject').val(),
            quotation_description: $('#quotation_description').val(), quotation_status: $('#quotation_status').val(),
            quotation_vat: vatPercent, quotation_vat_type: vatType, quotation_vat_amount: vatAmount,
            quotation_amount: grandTotal, services: servicesList, _token: '{{ csrf_token() }}'
        };

        var saveBtn = $('#saveQuotationBtn');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: "{{ url('admin/job-quotations/store') }}", type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message, timer: 2000, showConfirmButton: false }).then(function() {
                        window.location.href = "{{ url('admin/job-quotations/create') }}?job_id=" + selectedJobId;
                    });
                } else { Swal.fire('Error!', response.message || 'Failed to save', 'error'); saveBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Quotation'); }
            },
            error: function() { Swal.fire('Error!', 'Failed to save quotation', 'error'); saveBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save Quotation'); }
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

    $(document).on('click', '#convertToInvoiceBtn', function() {
    if (!selectedJobId || servicesList.length === 0) return;

    Swal.fire({
        title: 'Convert to Invoice?',
        text: 'This will create an invoice from the current quotation.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Yes, convert it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('admin/job-quotations/convert-to-invoice') }}",
                type: 'POST',
                data: {
                    job_id: selectedJobId,
                    services: servicesList,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            window.location.href = "{{ url('admin/job-invoices/create') }}?job_id=" + selectedJobId;
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to convert', 'error');
                }
            });
        }
    });
});

    $(document).on('click', '#printQuotationBtn', function() {
        if (!selectedJobId) return;
        Swal.fire({ title: 'Preparing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({
            url: "{{ url('admin/job-books/print') }}", type: 'POST',
            data: { _token: "{{ csrf_token() }}", job_id: selectedJobId, documents: ['quotation'] },
            success: function(response) { Swal.close(); if (response.success && response.html) { var printTab = window.open(); printTab.document.write(response.html); printTab.document.close(); } }
        });
    });

    function escapeHtml(str) { if (!str) return ''; return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});
</script>
@endsection
