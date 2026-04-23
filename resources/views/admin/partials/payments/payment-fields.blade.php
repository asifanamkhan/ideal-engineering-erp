<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Amount</h6>
                <h4 class="mb-0 text-primary" id="total_amount">0.00</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Paid Amount</h6>
                <h4 class="mb-0 text-success" id="paid_amount">0.00</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Due Amount</h6>
                <h4 class="mb-0 text-danger" id="due_amount">0.00</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Payment Amount <span class="text-danger">*</span></label>
        <input type="number" name="payment_amount" id="payment_amount" class="form-control" step="0.01">
        <small class="text-muted">Max: <span id="max_due">0.00</span></small>
    </div>
    <div class="col-md-6 mb-3">
        <label>Payment Mode <span class="text-danger">*</span></label>
        <select name="payment_mode_id" id="payment_mode_id" class="form-control">
            <option value="">Select Payment Mode</option>
            @foreach($paymentModes as $mode)
            <option
            @if ($mode->mode_name == 'Cash')
            selected
            @endif
            value="{{ $mode->id }}">{{ $mode->mode_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12 mb-3">
        <label>Narration</label>
        <textarea name="narration" id="narration" class="form-control" rows="2"></textarea>
    </div>
</div>

<!-- Dynamic Fields -->
<div id="cash_fields" class="dynamic-fields" style="display: none;">
    <div class="alert alert-info">Cash payment selected.</div>
</div>

<div id="cheque_fields" class="dynamic-fields" style="display: none;">
    <div class="card border-warning mb-3">
        <div class="card-header bg-warning">Cheque Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6"><input type="text" name="chq_no" class="form-control" placeholder="Cheque No"></div>
                <div class="col-md-6"><input type="date" name="chq_date" class="form-control"></div>
            </div>
        </div>
    </div>
</div>

<div id="card_fields" class="dynamic-fields" style="display: none;">
    <div class="card border-info mb-3">
        <div class="card-header bg-info">Card Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><input type="text" name="card_no" class="form-control" placeholder="Card No"></div>
                <div class="col-md-4"><input type="text" name="online_trx_id" class="form-control" placeholder="Transaction ID"></div>
                <div class="col-md-4"><input type="date" name="online_trx_dt" class="form-control"></div>
            </div>
        </div>
    </div>
</div>

<div id="mobile_banking_fields" class="dynamic-fields" style="display: none;">
    <div class="card border-success mb-3">
        <div class="card-header bg-success">Mobile Banking</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <select name="mfs_name" class="form-control">
                        <option value="">Select MFS</option>
                        <option value="Bkash">Bkash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Rocket">Rocket</option>
                    </select>
                </div>
                <div class="col-md-4"><input type="text" name="online_trx_id" class="form-control" placeholder="Transaction ID"></div>
                <div class="col-md-4"><input type="date" name="online_trx_dt" class="form-control"></div>
            </div>
        </div>
    </div>
</div>

<div id="internet_banking_fields" class="dynamic-fields" style="display: none;">
    <div class="card border-secondary mb-3">
        <div class="card-header bg-secondary">Internet Banking</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select name="bank_code" class="form-control">
                        <option value="">Select Bank</option>
                        @foreach($bankInfos as $bank)
                        <option value="{{ $bank->bank_code }}">{{ $bank->bank_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input type="text" name="bank_ac_no" class="form-control" placeholder="Account No"></div>
                <div class="col-md-3"><input type="text" name="online_trx_id" class="form-control" placeholder="Transaction ID"></div>
                <div class="col-md-3"><input type="date" name="online_trx_dt" class="form-control"></div>
            </div>
        </div>
    </div>
</div>

<div id="giftcard_fields" class="dynamic-fields" style="display: none;">
    <div class="alert alert-secondary">Gift Card/Points selected.</div>
</div>
