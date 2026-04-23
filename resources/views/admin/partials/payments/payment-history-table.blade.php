@props(['inline' => false])

@if($inline)
<div class="table-responsive">
    <table class="table table-sm table-hover" id="payment-history-table-inline">
        <thead class="table-head">
            <tr><th>SL</th><th>Date</th><th>Amount</th><th>Mode</th><th>Narration</th><th>Actions</th></tr>
        </thead>
        <tbody id="payment-history-body_inline">  <!-- এখানে _inline ব্যবহার করো -->
            <tr><td colspan="6" class="text-center">Loading...</td></tr>
        </tbody>
    </table>
</div>
@else
<div class="table-responsive">
    <table class="table table-sm table-hover" id="payment-history-table">
        <thead class="table-head">
            <tr><th>SL</th><th>Date</th><th>Amount</th><th>Mode</th><th>Narration</th><th>Actions</th></tr>
        </thead>
        <tbody id="payment-history-body">
            <tr><td colspan="6" class="text-center">Loading...</td></tr>
        </tbody>
    </table>
</div>
@endif
