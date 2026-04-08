<form id="customerForm">
    @csrf
    <input type="hidden" id="customer_id" name="customer_id">
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label for="name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control " id="name" name="name" placeholder="Enter customer name" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="email">Email</label>
                <input type="email" class="form-control " id="email" name="email" placeholder="customer@example.com">
                
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="phone">Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control " id="phone" name="phone" placeholder="Enter phone number" required>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="type">Customer Type <span class="text-danger">*</span></label>
                <select class="form-control " id="type" name="type" required>
                    <option value="individual">Individual</option>
                    <option value="business">Business</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="reference">Reference</label>
                <input type="text" class="form-control " id="reference" name="reference" placeholder="Reference person/company">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label for="address">Address</label>
                <textarea class="form-control " id="address" name="address" rows="2" placeholder="Enter address"></textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="opening_bal">Opening Balance</label>
                <input type="number" step="0.01" class="form-control " id="opening_bal" name="opening_bal" value="0" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="status">Status</label>
                <select class="form-control " id="status" name="status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Business Fields (Hidden by default) -->
    <div class="business-fields" style="display: none;">
        <hr>
        <h6 class="mb-3"><i class="fas fa-building"></i> Business Information</h6>
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label for="business_name">Business Name</label>
                    <input type="text" class="form-control " id="business_name" name="business_name" placeholder="Business/Company name">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="business_phone">Business Phone</label>
                    <input type="text" class="form-control " id="business_phone" name="business_phone" placeholder="Business phone number">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="tax_no">Tax Number</label>
                    <input type="text" class="form-control " id="tax_no" name="tax_no" placeholder="Tax/Vat number">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label for="business_address">Business Address</label>
                    <textarea class="form-control " id="business_address" name="business_address" rows="2" placeholder="Business address"></textarea>
                </div>
            </div>
        </div>
    </div>

    
</form>

<script>
    // Toggle business fields based on type selection
    document.getElementById('type')?.addEventListener('change', function() {
        const businessFields = document.querySelector('.business-fields');
        if (this.value === 'business') {
            businessFields.style.display = 'block';
        } else {
            businessFields.style.display = 'none';
        }
    });
    
    // Trigger on page load if type is pre-selected
    if (document.getElementById('type')?.value === 'business') {
        document.querySelector('.business-fields').style.display = 'block';
    }
</script>