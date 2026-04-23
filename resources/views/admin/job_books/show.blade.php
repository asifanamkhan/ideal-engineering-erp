@extends('layouts.dashboard.app')
@section('css')
<style>
    .text-end{
        text-align: right;
    }
    .nav-pills .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 0.5rem;
        color: #4a5568;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link i {
        font-size: 0.85rem;
    }
    .nav-pills .nav-link.active {
        background-color: #4e73df;
        color: white;
        box-shadow: 0 2px 5px rgba(78, 115, 223, 0.3);
    }
    .nav-pills .nav-link:not(.active):hover {
        background-color: #e9ecef;
        color: #2c3e50;
    }
    .flex-nowrap {
        flex-wrap: nowrap;
    }
    .overflow-auto {
        overflow-x: auto;
        overflow-y: hidden;
    }
    .pb-1 {
        padding-bottom: 0.25rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid ">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i> Job Details</h4>
            </div>
            <div>
                <a href="{{ route('admin.job-books.index') }}" class="btn btn-primary shadow-sm px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to Jobs
                </a>
            </div>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-body">
            <!-- Header Card with Job Info & Actions -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3" style="gap: 12px">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-tasks fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Job #{{ $jobBook->job_id ?? 'N/A' }}</h5>
                                        <div class="d-flex flex-wrap mt-1 text-muted" style="gap: 12px">
                                            <span><i class="fas fa-user me-1"></i> Customer: {{ $jobBook->customer_name ?? 'N/A' }}</span>
                                            <span><i class="fas fa-calendar me-1"></i> Job Date: {{ $jobBook->job_date ? \Carbon\Carbon::parse($jobBook->job_date)->format('d M, Y') : 'N/A' }}</span>
                                            <span class="badge {{ $jobBook->job_status == 'completed' ? 'bg-success' : ($jobBook->job_status == 'in_progress' ? 'bg-info' : ($jobBook->job_status == 'pending' ? 'bg-warning' : 'bg-secondary')) }}">
                                                <i class="fas fa-circle me-1 small"></i> {{ ucfirst(str_replace('_', ' ', $jobBook->job_status ?? 'Pending')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex " style="gap: 12px">
                                    <button type="button" class="btn btn-sm btn-success print-btn" data-id="{{ $jobBook->id }}">
                                        <i class="fas fa-print me-1"></i> Print
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary payment-btn" data-id="{{ $jobBook->id }}" data-customer-id="{{ $jobBook->customer_id }}">
                                        <i class="fas fa-money-bill-wave me-1"></i> Payment
                                    </button>
                                    <a href="{{ route('admin.job-books.edit', $jobBook->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $jobBook->id }}">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compact Nav Pills -->
            <div class="mb-3">
                <ul class="nav nav-pills gap-1 flex-nowrap overflow-auto pb-1" style="gap: 0.25rem !important;" id="jobBookTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="job-tab" data-bs-toggle="pill" data-bs-target="#job" type="button" role="tab">
                            <i class="fas fa-briefcase me-1"></i> Job
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="parts-tab" data-bs-toggle="pill" data-bs-target="#parts" type="button" role="tab">
                            <i class="fas fa-microchip me-1"></i> Parts
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="quotations-tab" data-bs-toggle="pill" data-bs-target="#quotations" type="button" role="tab">
                            <i class="fas fa-file-alt me-1"></i> Quotations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="invoices-tab" data-bs-toggle="pill" data-bs-target="#invoices" type="button" role="tab">
                            <i class="fas fa-receipt me-1"></i> Invoices
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button" role="tab">
                            <i class="fas fa-credit-card me-1"></i> Payment
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">

                {{-- JOB TAB --}}
                <div class="tab-pane fade show active" id="job" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Job ID</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->job_id ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Branch ID</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->branch_id ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Customer</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->customer_name ?? 'N/A' }}</p>
                                        <small class="text-muted">Phone: {{ $jobBook->customer_phone ?? 'N/A' }}</small><br>
                                        <small class="text-muted">Address: {{ $jobBook->customer_address ?? 'N/A' }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Job Date</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->job_date ? \Carbon\Carbon::parse($jobBook->job_date)->format('d M, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Engine</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->engine ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Job Status</label>
                                        <p class="fw-semibold mb-0">
                                            <span class="badge {{ $jobBook->job_status == 'completed' ? 'bg-success' : ($jobBook->job_status == 'in_progress' ? 'bg-info' : ($jobBook->job_status == 'pending' ? 'bg-warning' : 'bg-secondary')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $jobBook->job_status ?? 'Pending')) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Parts Status</label>
                                        <p class="fw-semibold mb-0">
                                            <span class="badge {{ $jobBook->parts_status == 'received' ? 'bg-success' : ($jobBook->parts_status == 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                                {{ ucfirst(str_replace('_', ' ', $jobBook->parts_status ?? 'Not Received')) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Delivery Date</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->delivery_date ? \Carbon\Carbon::parse($jobBook->delivery_date)->format('d M, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Assign To</label>
                                        <p class="fw-semibold mb-0">{{ $jobBook->assign_to_names ?? $jobBook->assign_to ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Created By</label>
                                        <p class="fw-semibold mb-0">
                                            @php
                                                $createdUser = DB::table('users')->where('id', $jobBook->created_by)->first();
                                            @endphp
                                            {{ $createdUser->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Descriptions</label>
                                        <p class="mb-0">{{ $jobBook->descriptions ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($jobBook->documents)
                                <div class="col-12">
                                    <div class="info-item p-2 bg-light rounded">
                                        <label class="text-muted small mb-0">Documents</label>
                                        <p class="mb-0">
                                            <a href="{{ asset($jobBook->documents) }}" target="_blank" class="btn btn-sm btn-link p-0">
                                                <i class="fas fa-file-download me-1"></i> View Document
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PARTS TAB --}}
                <div class="tab-pane fade" id="parts" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-head">
                                        <tr>
                                            <th class="ps-3">Part Name</th>
                                            <th>Part No</th>
                                            <th>Size</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end pe-3">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($jobParts as $part)
                                        <tr>
                                            <td class="ps-3">{{ $part->part_name ?? 'N/A' }}</td>
                                            <td>{{ $part->part_no ?? 'N/A' }}</td>
                                            <td>{{ $part->size_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $part->quantity ?? '0' }}</td>
                                            <td class="text-end">৳ {{ number_format($part->single_price ?? 0, 2) }}</td>
                                            <td class="text-end pe-3">৳ {{ number_format($part->total_price ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No parts records found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if($jobParts->count() > 0)
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="5" class="text-end fw-bold">Total Parts Amount:</th>
                                            <th class="text-end pe-3 fw-bold">৳ {{ number_format($jobParts->sum('total_price'), 2) }}</th>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- QUOTATIONS TAB --}}
                <div class="tab-pane fade" id="quotations" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-head">
                                        <tr>
                                            <th class="ps-3">Service Name</th>
                                            <th>Unit</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end pe-3">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($quotations as $quotation)
                                        <tr>
                                            <td class="ps-3">{{ $quotation->service_name ?? 'N/A' }}</td>
                                            <td>{{ $quotation->unit_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $quotation->quantity ?? '0' }}</td>
                                            <td class="text-end">৳ {{ number_format($quotation->price ?? 0, 2) }}</td>
                                            <td class="text-end pe-3">৳ {{ number_format($quotation->total_price ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No quotations found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Quotation Amount:</th>
                                            <th class="text-end pe-3 fw-bold">৳ {{ number_format($jobBook->quotation_amount ?? 0, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Status:</th>
                                            <th class="text-end pe-3">
                                                <span class="badge {{ $jobBook->quotation_status == 'send' ? 'bg-success' : ($jobBook->quotation_status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $jobBook->quotation_status ?? 'Not Send')) }}
                                                </span>
                                            </th>
                                        </tr>
                                        @if($jobBook->quotation_description)
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Description:</th>
                                            <th class="text-end pe-3">{{ $jobBook->quotation_description }}</th>
                                        </tr>
                                        @endif
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INVOICES TAB --}}
                <div class="tab-pane fade" id="invoices" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-head">
                                        <tr>
                                            <th class="ps-3">Service Name</th>
                                            <th>Unit</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end pe-3">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoices as $invoice)
                                        <tr>
                                            <td class="ps-3">{{ $invoice->service_name ?? 'N/A' }}</td>
                                            <td>{{ $invoice->unit_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $invoice->quantity ?? '0' }}</td>
                                            <td class="text-end">৳ {{ number_format($invoice->price ?? 0, 2) }}</td>
                                            <td class="text-end pe-3">৳ {{ number_format($invoice->total_price ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No invoices found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Discount:</th>
                                            <th class="text-end pe-3 fw-bold text-danger">- ৳ {{ number_format($jobBook->invoice_discount ?? 0, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Total Amount:</th>
                                            <th class="text-end pe-3 fw-bold">৳ {{ number_format($jobBook->invoice_amount ?? 0, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Status:</th>
                                            <th class="text-end pe-3">
                                                <span class="badge {{ $jobBook->invoice_status == 'paid' ? 'bg-success' : ($jobBook->invoice_status == 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ ucfirst($jobBook->invoice_status ?? 'Unpaid') }}
                                                </span>
                                            </th>
                                        </tr>
                                        @if($jobBook->invoice_description)
                                        <tr>
                                            <th colspan="4" class="text-end fw-bold">Description:</th>
                                            <th class="text-end pe-3">{{ $jobBook->invoice_description }}</th>
                                        </tr>
                                        @endif
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PAYMENT TAB --}}
                <div class="tab-pane fade" id="payment" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <!-- Payment Summary Cards -->
                            @php
                                $totalPaid = DB::table('payments')->where('type', 'job')->where('type_id', $jobBook->id)->sum('amount');
                                $dueAmount = ($jobBook->invoice_amount ?? 0) - ($jobBook->invoice_discount ?? 0) - $totalPaid;
                                $paymentCount = DB::table('payments')->where('type', 'job')->where('type_id', $jobBook->id)->count();
                            @endphp
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Total Invoice</h6>
                                            <h4 class="mb-0 text-primary">৳ {{ number_format($jobBook->invoice_amount ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Discount</h6>
                                            <h4 class="mb-0 text-warning">- ৳ {{ number_format($jobBook->invoice_discount ?? 0, 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Paid Amount</h6>
                                            <h4 class="mb-0 text-success">৳ {{ number_format($totalPaid, 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Due Amount</h6>
                                            <h4 class="mb-0 text-danger">৳ {{ number_format($dueAmount, 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment History Table -->
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="show-payment-table">
                                    <thead class="table-head">
                                        <tr>
                                            <th>SL</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Narration</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="show-payment-history-body">
                                        <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this job? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Options Modal -->
<div class="modal fade" id="printOptionsModal" tabindex="-1" role="dialog" aria-labelledby="printOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printOptionsModalLabel">
                    <i class="fas fa-print me-2"></i> Print Options
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="fw-bold mb-3">Select documents to print:</p>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input print-option" value="parts" id="printParts">
                    <label class="form-check-label fw-bold" for="printParts">Parts List</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input print-option" value="quotation" id="printQuotation">
                    <label class="form-check-label fw-bold" for="printQuotation">Quotation</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input print-option" value="invoice" id="printInvoice">
                    <label class="form-check-label fw-bold" for="printInvoice">Invoice</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmPrint">Print</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal Section -->
@include('admin.partials.payments.payment-section', ['inline' => false])

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('admin.partials.payments.payment-script')

<script>
    $('#jobs-sidebar, #jobs-index-sidebar').addClass('active');
    $('#collapseJobs').addClass('show');

    // Load payment history for show page
    function loadShowPaymentHistory() {
        var jobId = {{ $jobBook->id }};
        $.ajax({
            url: "{{ url('admin/job-books/get-payment-history') }}/" + jobId,
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(i, p) {
                        html += `<tr>
                            <td>${i+1}</td>
                            <td>${p.payment_date || '-'}</td>
                            <td>৳ ${parseFloat(p.amount).toFixed(2)}</td>
                            <td>${p.payment_mode || '-'}</td>
                            <td>${p.narration || '-'}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button type="button" class="dropdown-item view-payment" data-id="${p.id}">
                                                <i class="fas fa-eye me-2 text-info"></i> View
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item edit-payment" data-id="${p.id}">
                                                <i class="fas fa-edit me-2 text-primary"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item delete-payment" data-id="${p.id}">
                                                <i class="fas fa-trash me-2 text-danger"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>`;
                    });
                    $('#show-payment-history-body').html(html);
                } else {
                    $('#show-payment-history-body').html('<tr><td colspan="6" class="text-center">No payment records found</td></tr>');
                }
            },
            error: function() {
                $('#show-payment-history-body').html('<tr><td colspan="6" class="text-center text-danger">Failed to load payment history</td></tr>');
            }
        });
    }

    $(document).ready(function() {
        loadShowPaymentHistory();

        // Delete button handler
        $('.delete-btn').on('click', function() {
            let id = $(this).data('id');
            if (confirm('Are you sure you want to delete this job record?')) {
                $.ajax({
                    url: "{{ route('admin.job-books.destroy', ['job_book' => ':id']) }}".replace(':id', id),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = '{{ route("admin.job-books.index") }}';
                        } else {
                            alert('Error deleting record');
                        }
                    },
                    error: function(xhr) {
                        alert('Something went wrong: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    }
                });
            }
        });
    });

    // Print functionality
    var printJobId = null;
    $(document).on('click', '.print-btn', function() {
        printJobId = $(this).data('id');
        $('.print-option').prop('checked', false);
        $('#printOptionsModal').modal('show');
    });

    $('#confirmPrint').click(function() {
        var selectedOptions = [];
        $('.print-option:checked').each(function() {
            selectedOptions.push($(this).val());
        });
        if (selectedOptions.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one document to print.' });
            return;
        }
        $('#printOptionsModal').modal('hide');
        Swal.fire({ title: 'Preparing documents...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({
            url: "{{ url('admin/job-books/print') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", job_id: printJobId, documents: selectedOptions },
            success: function(response) {
                Swal.close();
                if (response.success && response.html) {
                    var printTab = window.open();
                    printTab.document.write(response.html);
                    printTab.document.close();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: response.message || 'Failed to generate print documents.' });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'An error occurred.' });
            }
        });
    });

    // Payment button handler
    $(document).on('click', '.payment-btn', function() {
        var jobId = $(this).data('id');
        var customerId = $(this).data('customer-id');

        PaymentModal.init({
            paymentFor: 'customer',
            paymentForId: customerId,
            type: 'job',
            typeId: jobId,
            getDetailsUrl: "{{ url('admin/job-books/get-payment-details') }}/" + jobId,
            processUrl: "{{ url('admin/job-books/process-payment') }}",
            updateUrl: "{{ url('admin/job-books/update-payment') }}",
            getHistoryUrl: "{{ url('admin/job-books/get-payment-history') }}",
            getPaymentUrl: "{{ url('admin/job-books/get-payment') }}",
            deleteUrl: "{{ url('admin/job-books/delete-payment') }}",
            modalTitle: 'Job Payment'
        });
    });

    // Refresh payment history after payment success
    $(document).on('paymentSuccess', function() {
        loadShowPaymentHistory();
        location.reload();
    });
</script>
@endsection
