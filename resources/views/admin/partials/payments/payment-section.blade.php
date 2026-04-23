@php
$paymentModes = DB::table('acc_payment_modes')->get();
$bankInfos = DB::table('acc_bank_info')->get();
@endphp

@props(['inline' => false])

@if(!$inline)
<!-- Modal Version -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> <span id="paymentModalTitle">Process Payment</span></h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="px-3 pt-3">
                <ul class="nav nav-pills gap-1 flex-nowrap overflow-auto pb-1" style="gap: 0.25rem !important;" id="paymentTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="payment-tab" data-bs-toggle="pill" data-bs-target="#paymentFormTab" type="button" role="tab">
                            <i class="fas fa-credit-card me-1"></i> New Payment
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#paymentHistoryTab" type="button" role="tab">
                            <i class="fas fa-history me-1"></i> Payment History
                            <span class="badge bg-success ms-1" id="payment_count">0</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content p-3">
                @include('admin.partials.payments.payment-form-content', ['paymentModes' => $paymentModes, 'bankInfos' => $bankInfos])
            </div>
        </div>
    </div>
</div>
@else
<!-- Inline Version (No Modal) -->
<div id="paymentInlineSection" class="payment-inline-section mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> <span id="paymentModalTitle">Payment Management</span></h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills gap-1 mb-3 flex-nowrap overflow-auto" style="gap: 0.25rem !important;" id="paymentTabsInline" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="payment-tab-inline" data-bs-toggle="pill" data-bs-target="#paymentFormTabInline" type="button" role="tab">
                        <i class="fas fa-credit-card me-1"></i> New Payment
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="history-tab-inline" data-bs-toggle="pill" data-bs-target="#paymentHistoryTabInline" type="button" role="tab">
                        <i class="fas fa-history me-1"></i> Payment History
                        <span class="badge bg-success ms-1" id="payment_count_inline">0</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                @include('admin.partials.payments.payment-form-content', ['paymentModes' => $paymentModes, 'bankInfos' => $bankInfos, 'inline' => true])
            </div>
        </div>
    </div>
</div>
@endif

<!-- View Payment Details Modal (Same for both) -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Payment Details</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="paymentDetailsBody">Loading...</div>
        </div>
    </div>
</div>
