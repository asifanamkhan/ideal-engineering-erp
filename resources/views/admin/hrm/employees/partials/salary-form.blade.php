<div class="card shadow mb-4">
    <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Salary Information</h6>
    </div>
    <div class="card-body">
        <form id="salaryForm">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="basic_salary">Basic Salary <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="basic_salary" name="basic_salary" 
                               value="{{ $employee->basic_salary ?? 0 }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="total_allowance">Total Allowance</label>
                        <input type="number" step="0.01" class="form-control" id="total_allowance" name="total_allowance" 
                               value="{{ $employee->total_allowance ?? 0 }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="total_deduction">Total Deduction</label>
                        <input type="number" step="0.01" class="form-control" id="total_deduction" name="total_deduction" 
                               value="{{ $employee->total_deduction ?? 0 }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="salary-summary" style="background: #dcf2f7; color: #000; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <small class="text-muted">Basic Salary</small>
                                <h5 id="display_basic" class="mb-0 font-weight-bold" style="color: #2c3e50;">0.00</h5>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Total Allowance</small>
                                <h5 id="display_allowance" class="mb-0 font-weight-bold" style="color: #2c3e50;">0.00</h5>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Total Deduction</small>
                                <h5 id="display_deduction" class="mb-0 font-weight-bold" style="color: #2c3e50;">0.00</h5>
                            </div>
                        </div>
                        <hr class="my-2" style="border-color: #c0c0c0;">
                        <div class="row text-center">
                            <div class="col-md-12">
                                <small class="text-muted">Gross Salary (Basic + Allowance - Deduction)</small>
                                <h4 id="gross_salary" class="mb-0 font-weight-bold" style="color: #28a745;">0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label for="overtime_rate">Overtime Rate (per day)</label>
                        <input type="number" step="0.01" class="form-control" id="overtime_rate" name="overtime_rate" 
                               value="{{ $employee->overtime_rate ?? 0 }}" placeholder="0.00">
                        <small class="text-muted">Amount per overtime day</small>
                    </div>
                </div>
                
            </div>

            <div class="text-center mt-3">
                <button type="button" id="saveSalaryBtn" class="btn btn-primary px-5 py-2">
                    <i class="fas fa-save"></i> Save Salary Information
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateGross() {
        let basic = parseFloat($('#basic_salary').val()) || 0;
        let allowance = parseFloat($('#total_allowance').val()) || 0;
        let deduction = parseFloat($('#total_deduction').val()) || 0;
        
        let gross = basic + allowance - deduction;
        
        // Update display
        $('#display_basic').text(basic.toFixed(2));
        $('#display_allowance').text(allowance.toFixed(2));
        $('#display_deduction').text(deduction.toFixed(2));
        $('#gross_salary').text(gross.toFixed(2));
    }
    
    $(document).ready(function() {
        // Bind change events
        $('#basic_salary, #total_allowance, #total_deduction').on('keyup change', function() {
            calculateGross();
        });
        
        // Initial calculation
        calculateGross();
    });
</script>