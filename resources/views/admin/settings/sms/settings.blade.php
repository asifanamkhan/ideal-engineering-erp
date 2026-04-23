@extends('layouts.dashboard.app')

@section('title', 'SMS Gateway Settings')

@section('css')
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 22px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
        border-radius: 22px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #4e73df;
    }
    input:checked + .slider:before {
        transform: translateX(24px);
    }
    .card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 12px 20px;
    }
    .card-header h6 {
        font-size: 15px;
        font-weight: 600;
    }
    .module-badge {
        background: #f0f2f5;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
    }
    .table th, .table td {
        vertical-align: middle;
        padding: 10px 8px;
        font-size: 12px;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fc;
    }
    .template-preview {
        font-size: 10px;
        color: #6c757d;
        margin-top: 5px;
        padding: 4px 6px;
        background: #f8f9fc;
        border-radius: 4px;
    }
    .form-control, .form-select {
        font-size: 12px;
        padding: 5px 10px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .card-body {
        padding: 16px 20px;
    }
    .demo-btn {
        padding: 3px 8px;
        font-size: 10px;
        white-space: nowrap;
    }
    .template-wrapper {
        position: relative;
    }
    .table-responsive {
        overflow-x: auto;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0"><i class="fas fa-envelope me-2"></i> SMS Gateway Configuration</h4>
                <span class="text-muted small">Configure SMS gateway and notification templates</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-info" id="generateAllDemoTemplates">
                    <i class="fas fa-magic me-1"></i> Generate All Demo Templates
                </button>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.sms.settings.update') }}" method="POST" id="smsSettingsForm">
        @csrf

        <!-- Gateway Credentials -->
        <div class="card shadow mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-plug me-2"></i> Gateway Credentials</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">API URL <span class="text-danger">*</span></label>
                        <input type="text" name="api_url" class="form-control form-control-sm"
                            value="{{ $gateway->api_url ?? '' }}" placeholder="https://api.smsgateway.com/send" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <input type="text" name="api_key" class="form-control form-control-sm"
                            value="{{ $gateway->api_key ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">API Secret <span class="text-danger">*</span></label>
                        <input type="text" name="api_secret" class="form-control form-control-sm"
                            value="{{ $gateway->api_secret ?? '' }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sender ID</label>
                        <input type="text" name="sender_id" class="form-control form-control-sm"
                            value="{{ $gateway->sender_id ?? '' }}" placeholder="IDEALENG">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label d-block">Status</label>
                        <label class="switch">
                            <input type="checkbox" name="gateway_status" value="1"
                                @if(($gateway->status ?? 0) == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                        <span class="small ms-1">{{ ($gateway->status ?? 0) == 1 ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Phone Number -->
        <div class="card shadow mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i> Admin Notification</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label">Admin Phone Number</label>
                        <input type="text" name="admin_phone" class="form-control form-control-sm"
                            value="{{ $gateway->admin_phone ?? '' }}" placeholder="e.g., 017xxxxxxxx">
                        <small class="text-muted">Admin will receive SMS on this number</small>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-info mb-0 py-2 small">
                            <i class="fas fa-info-circle me-1"></i> Admin will receive SMS for all important events (New Job, Invoice, Payment, etc.)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module: JOB -->
        <div class="card shadow mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-briefcase me-2"></i> JOB Module</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th width="3%">#</th>
                                <th width="12%">Event</th>
                                <th width="6%">Party</th>
                                <th width="6%">Admin</th>
                                <th width="30%">Party Template</th>
                                <th width="30%">Admin Template</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $jobModules = [
                                'job_create' => ['icon' => 'plus-circle', 'label' => 'Job Created'],
                                'quotation_create' => ['icon' => 'file-alt', 'label' => 'Quotation Created'],
                                'quotation_send' => ['icon' => 'paper-plane', 'label' => 'Quotation Sent'],
                                'invoice_create' => ['icon' => 'receipt', 'label' => 'Invoice Created'],
                                'invoice_send' => ['icon' => 'send', 'label' => 'Invoice Sent']
                            ]; @endphp
                            @foreach($jobModules as $module => $info)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="module-badge">
                                        <i class="fas fa-{{ $info['icon'] }} me-1"></i> {{ $info['label'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <label class="switch mx-auto" style="display: inline-block;">
                                        <input type="checkbox" name="templates[job][{{ $module }}][party_status]" value="1"
                                            @if(($templates['job'][$module]['party_status'] ?? 0) == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switch mx-auto" style="display: inline-block;">
                                        <input type="checkbox" name="templates[job][{{ $module }}][admin_status]" value="1"
                                            @if(($templates['job'][$module]['admin_status'] ?? 0) == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="template-wrapper">
                                        <textarea class="form-control form-control-sm" name="templates[job][{{ $module }}][sms_text]"
                                            rows="2" placeholder="Party SMS template..." id="party_template_job_{{ $module }}">{{ $templates['job'][$module]['sms_text'] ?? '' }}</textarea>
                                        <div class="template-preview">
                                            <i class="fas fa-info-circle"></i>
                                            {job_id}, {customer_name}, {customer_phone}, {job_date}, {total_amount}, {due_amount}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="template-wrapper">
                                        <textarea class="form-control form-control-sm" name="templates[job][{{ $module }}][admin_sms_text]"
                                            rows="2" placeholder="Admin SMS template..." id="admin_template_job_{{ $module }}">{{ $templates['job'][$module]['admin_sms_text'] ?? '' }}</textarea>
                                        <div class="template-preview">
                                            <i class="fas fa-info-circle"></i>
                                            {job_id}, {customer_name}, {amount}, {status}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info demo-btn mb-1 w-100" onclick="generateDemoTemplate('job', '{{ $module }}', 'party')">
                                        <i class="fas fa-magic me-1"></i> Party
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary demo-btn w-100" onclick="generateDemoTemplate('job', '{{ $module }}', 'admin')">
                                        <i class="fas fa-user-shield me-1"></i> Admin
                                    </button>
                                 </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Module: PAYMENTS -->
        <div class="card shadow mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i> PAYMENTS Module</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th width="3%">#</th>
                                <th width="12%">Event</th>
                                <th width="6%">Party</th>
                                <th width="6%">Admin</th>
                                <th width="30%">Party Template</th>
                                <th width="30%">Admin Template</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $paymentModules = [
                                'job_payment' => ['icon' => 'tools', 'label' => 'Job Payment'],
                                'expense_payment' => ['icon' => 'money-bill-wave', 'label' => 'Expense Payment'],
                                'salary_payment' => ['icon' => 'user-circle', 'label' => 'Salary Payment'],
                                'purchase_payment' => ['icon' => 'shopping-cart', 'label' => 'Purchase Payment']
                            ]; @endphp
                            @foreach($paymentModules as $module => $info)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="module-badge">
                                        <i class="fas fa-{{ $info['icon'] }} me-1"></i> {{ $info['label'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <label class="switch mx-auto" style="display: inline-block;">
                                        <input type="checkbox" name="templates[payment][{{ $module }}][party_status]" value="1"
                                            @if(($templates['payment'][$module]['party_status'] ?? 0) == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switch mx-auto" style="display: inline-block;">
                                        <input type="checkbox" name="templates[payment][{{ $module }}][admin_status]" value="1"
                                            @if(($templates['payment'][$module]['admin_status'] ?? 0) == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="template-wrapper">
                                        <textarea class="form-control form-control-sm" name="templates[payment][{{ $module }}][sms_text]"
                                            rows="2" placeholder="Party SMS template..." id="party_template_payment_{{ $module }}">{{ $templates['payment'][$module]['sms_text'] ?? '' }}</textarea>
                                        <div class="template-preview">
                                            <i class="fas fa-info-circle"></i>
                                            {payment_id}, {customer_name}, {amount}, {payment_date}, {payment_mode}, {due_amount}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="template-wrapper">
                                        <textarea class="form-control form-control-sm" name="templates[payment][{{ $module }}][admin_sms_text]"
                                            rows="2" placeholder="Admin SMS template..." id="admin_template_payment_{{ $module }}">{{ $templates['payment'][$module]['admin_sms_text'] ?? '' }}</textarea>
                                        <div class="template-preview">
                                            <i class="fas fa-info-circle"></i>
                                            {payment_id}, {customer_name}, {amount}, {payment_mode}, {due_amount}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info demo-btn mb-1 w-100" onclick="generateDemoTemplate('payment', '{{ $module }}', 'party')">
                                        <i class="fas fa-magic me-1"></i> Party
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary demo-btn w-100" onclick="generateDemoTemplate('payment', '{{ $module }}', 'admin')">
                                        <i class="fas fa-user-shield me-1"></i> Admin
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Module: EXPENSE -->
        <div class="card shadow mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> EXPENSE Module</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th width="3%">#</th>
                                <th width="12%">Event</th>
                                <th width="6%">Party</th>
                                <th width="6%">Admin</th>
                                <th width="30%">Party Template</th>
                                <th width="30%">Admin Template</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $expenseModules = [
                                'expense_create' => ['icon' => 'plus-circle', 'label' => 'Expense Created'],
                                'expense_approve' => ['icon' => 'check-circle', 'label' => 'Expense Approved']
                            ]; @endphp
                            @foreach($expenseModules as $module => $info)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="module-badge">
                                        <i class="fas fa-{{ $info['icon'] }} me-1"></i> {{ $info['label'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <label class="switch mx-auto" style="display: inline-block;">
                                        <input type="checkbox" name="templates[expense][{{ $module }}][party_status]" value="1"
                                            @if(($templates['expense'][$module]['party_status'] ?? 0) == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switch mx-auto" style="display: inline-block;">
                                        <input type="checkbox" name="templates[expense][{{ $module }}][admin_status]" value="1"
                                            @if(($templates['expense'][$module]['admin_status'] ?? 0) == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="template-wrapper">
                                        <textarea class="form-control form-control-sm" name="templates[expense][{{ $module }}][sms_text]"
                                            rows="2" placeholder="Party SMS template..." id="party_template_expense_{{ $module }}">{{ $templates['expense'][$module]['sms_text'] ?? '' }}</textarea>
                                        <div class="template-preview">
                                            <i class="fas fa-info-circle"></i>
                                            {expense_no}, {amount}, {date}, {category}, {narration}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="template-wrapper">
                                        <textarea class="form-control form-control-sm" name="templates[expense][{{ $module }}][admin_sms_text]"
                                            rows="2" placeholder="Admin SMS template..." id="admin_template_expense_{{ $module }}">{{ $templates['expense'][$module]['admin_sms_text'] ?? '' }}</textarea>
                                        <div class="template-preview">
                                            <i class="fas fa-info-circle"></i>
                                            {expense_no}, {amount}, {category}, {date}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info demo-btn mb-1 w-100" onclick="generateDemoTemplate('expense', '{{ $module }}', 'party')">
                                        <i class="fas fa-magic me-1"></i> Party
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary demo-btn w-100" onclick="generateDemoTemplate('expense', '{{ $module }}', 'admin')">
                                        <i class="fas fa-user-shield me-1"></i> Admin
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-4 text-center">
            <button type="submit" class="btn btn-success px-5 py-2">
                <i class="fas fa-save me-2"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
    // Demo Templates Data (Party + Admin)
    const demoTemplates = {
        job: {
            party: {
                job_create: "Dear {customer_name},\n\nYour job has been created successfully.\nJob ID: {job_id}\nDate: {job_date}\n\nThank you.\nIdeal Engineering Works",
                quotation_create: "Dear {customer_name},\n\nQuotation has been created for your job.\nJob ID: {job_id}\nTotal Amount: {total_amount} Taka\n\nThank you.\nIdeal Engineering Works",
                quotation_send: "Dear {customer_name},\n\nQuotation has been sent to you.\nJob ID: {job_id}\nTotal Amount: {total_amount} Taka\n\nPlease review and confirm.\nIdeal Engineering Works",
                invoice_create: "Dear {customer_name},\n\nInvoice has been created for your job.\nJob ID: {job_id}\nTotal Amount: {total_amount} Taka\nDue Amount: {due_amount} Taka\n\nThank you.\nIdeal Engineering Works",
                invoice_send: "Dear {customer_name},\n\nInvoice has been sent to you.\nJob ID: {job_id}\nTotal Amount: {total_amount} Taka\nDue Amount: {due_amount} Taka\n\nPlease make payment.\nIdeal Engineering Works"
            },
            admin: {
                job_create: "New Job Created\nJob ID: {job_id}\nCustomer: {customer_name}\nDate: {job_date}",
                quotation_create: "Quotation Created\nJob ID: {job_id}\nCustomer: {customer_name}\nAmount: {total_amount} Taka",
                quotation_send: "Quotation Sent\nJob ID: {job_id}\nCustomer: {customer_name}\nAmount: {total_amount} Taka",
                invoice_create: "Invoice Created\nJob ID: {job_id}\nCustomer: {customer_name}\nAmount: {total_amount} Taka\nDue: {due_amount} Taka",
                invoice_send: "Invoice Sent\nJob ID: {job_id}\nCustomer: {customer_name}\nAmount: {total_amount} Taka\nDue: {due_amount} Taka"
            }
        },
        // Blade এ ডেমো টেমপ্লেট আপডেট করো
        payment: {
            party: {
                job_payment: "Dear {customer_name},\n\nPayment received successfully.\nTransaction ID: {tran_id}\nAmount: {amount} Taka\nPayment Mode: {payment_mode}\nDate: {payment_date}\nJob ID: {job_id}\nDue Amount: {due_amount} Taka\n\nThank you.\nIdeal Engineering Works",
                expense_payment: "Payment has been made for expense.\nTransaction ID: {tran_id}\nAmount: {amount} Taka\nDate: {payment_date}\nPayment Mode: {payment_mode}\nExpense No: {expense_no}\n\nIdeal Engineering Works",
                salary_payment: "Salary payment has been processed.\nTransaction ID: {tran_id}\nAmount: {amount} Taka\nDate: {payment_date}\nPayment Mode: {payment_mode}\n\nIdeal Engineering Works",
                purchase_payment: "Purchase payment has been made.\nTransaction ID: {tran_id}\nAmount: {amount} Taka\nDate: {payment_date}\nPayment Mode: {payment_mode}\n\nIdeal Engineering Works"
            },
            admin: {
                job_payment: "Payment Received\nTransaction: {tran_id}\nCustomer: {customer_name}\nAmount: {amount} Taka\nMode: {payment_mode}\nJob: {job_id}\nDue: {due_amount} Taka",
                expense_payment: "Expense Payment Made\nTransaction: {tran_id}\nAmount: {amount} Taka\nMode: {payment_mode}\nExpense: {expense_no}",
                salary_payment: "Salary Payment Processed\nTransaction: {tran_id}\nAmount: {amount} Taka\nMode: {payment_mode}",
                purchase_payment: "Purchase Payment Made\nTransaction: {tran_id}\nAmount: {amount} Taka\nMode: {payment_mode}"
            }
        },
        expense: {
            party: {
                expense_create: "Expense has been created.\nExpense No: {expense_no}\nAmount: {amount} Taka\nDate: {date}\nCategory: {category}\n\nIdeal Engineering Works",
                expense_approve: "Expense has been approved.\nExpense No: {expense_no}\nAmount: {amount} Taka\nDate: {date}\n\nIdeal Engineering Works"
            },
            admin: {
                expense_create: "Expense Created\nExpense No: {expense_no}\nAmount: {amount} Taka\nCategory: {category}\nDate: {date}",
                expense_approve: "Expense Approved\nExpense No: {expense_no}\nAmount: {amount} Taka"
            }
        }
    };

    function generateDemoTemplate(module, subModule, type) {
        let template = '';
        if (type === 'party') {
            if (demoTemplates[module] && demoTemplates[module].party && demoTemplates[module].party[subModule]) {
                template = demoTemplates[module].party[subModule];
            }
            const textareaId = `party_template_${module}_${subModule}`;
            document.getElementById(textareaId).value = template;
        } else {
            if (demoTemplates[module] && demoTemplates[module].admin && demoTemplates[module].admin[subModule]) {
                template = demoTemplates[module].admin[subModule];
            }
            const textareaId = `admin_template_${module}_${subModule}`;
            document.getElementById(textareaId).value = template;
        }

        Swal.fire({
            icon: 'success',
            title: `${type.toUpperCase()} Demo Template Generated!`,
            text: `${type} template has been added. You can edit it as needed.`,
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Generate all demo templates at once
    document.getElementById('generateAllDemoTemplates')?.addEventListener('click', function() {
        let count = 0;

        // Generate all job templates (party + admin)
        for (let key in demoTemplates.job.party) {
            if (document.getElementById(`party_template_job_${key}`)) {
                document.getElementById(`party_template_job_${key}`).value = demoTemplates.job.party[key];
                document.getElementById(`admin_template_job_${key}`).value = demoTemplates.job.admin[key];
                count += 2;
            }
        }

        // Generate all payment templates
        for (let key in demoTemplates.payment.party) {
            if (document.getElementById(`party_template_payment_${key}`)) {
                document.getElementById(`party_template_payment_${key}`).value = demoTemplates.payment.party[key];
                document.getElementById(`admin_template_payment_${key}`).value = demoTemplates.payment.admin[key];
                count += 2;
            }
        }

        // Generate all expense templates
        for (let key in demoTemplates.expense.party) {
            if (document.getElementById(`party_template_expense_${key}`)) {
                document.getElementById(`party_template_expense_${key}`).value = demoTemplates.expense.party[key];
                document.getElementById(`admin_template_expense_${key}`).value = demoTemplates.expense.admin[key];
                count += 2;
            }
        }

        Swal.fire({
            icon: 'success',
            title: 'All Demo Templates Generated!',
            text: `${count} templates (Party + Admin) have been added.`,
            timer: 3000,
            showConfirmButton: false
        });
    });
</script>
@endsection
