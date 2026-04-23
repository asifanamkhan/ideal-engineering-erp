@props(['paymentModes', 'bankInfos', 'inline' => false])

@if($inline)
<!-- Inline Tab Content (Invoice Page - No separate payment button) -->
<div class="tab-pane fade show active" id="paymentFormTabInline" role="tabpanel">
    <form id="paymentFormInline">
        @csrf
        <input type="hidden" name="payment_for" id="payment_for_inline">
        <input type="hidden" name="payment_for_id" id="payment_for_id_inline">
        <input type="hidden" name="type" id="type_inline">
        <input type="hidden" name="type_id" id="type_id_inline">
        <input type="hidden" name="payment_id" id="edit_payment_id_inline">

        @include('admin.partials.payments.payment-fields', ['paymentModes' => $paymentModes, 'bankInfos' => $bankInfos])

        <!-- NO BUTTONS HERE - Payment will be saved with invoice -->
    </form>
</div>
<div class="tab-pane fade" id="paymentHistoryTabInline" role="tabpanel">
    @include('admin.partials.payments.payment-history-table', ['inline' => true])
</div>
@else
<!-- Modal Tab Content (Index Page - Separate payment button) -->
<div class="tab-pane fade show active" id="paymentFormTab" role="tabpanel">
    <form id="paymentForm">
        @csrf
        <input type="hidden" name="payment_for" id="payment_for">
        <input type="hidden" name="payment_for_id" id="payment_for_id">
        <input type="hidden" name="type" id="type">
        <input type="hidden" name="type_id" id="type_id">
        <input type="hidden" name="payment_id" id="edit_payment_id">

        @include('admin.partials.payments.payment-fields', ['paymentModes' => $paymentModes, 'bankInfos' => $bankInfos])

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-check-circle"></i> Process Payment</button>
        </div>
    </form>
</div>
<div class="tab-pane fade" id="paymentHistoryTab" role="tabpanel">
    @include('admin.partials.payments.payment-history-table', ['inline' => false])
</div>
@endif
