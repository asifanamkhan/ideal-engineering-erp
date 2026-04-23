<?php

use App\Http\Controllers\Admin\Contacts\CustomerController;
use App\Http\Controllers\Admin\Contacts\SupplierController;
use App\Http\Controllers\Auth\CandidateRegisterController;
use App\Http\Controllers\Admin\Hrm\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\Expense\ExpenseCategoryController;
use App\Http\Controllers\Admin\Expense\ExpenseController;
use App\Http\Controllers\Admin\Hrm\DesignationController;
use App\Http\Controllers\Admin\JobBookController;
use App\Http\Controllers\Admin\Settings\PartsController;
use App\Http\Controllers\Admin\Settings\GenerelSettingController;
use App\Http\Controllers\Admin\Settings\BranchController;
use App\Http\Controllers\Admin\Settings\ServiceController;
use App\Http\Controllers\Admin\Settings\SizeController;
use App\Http\Controllers\Admin\Settings\UnitController;
use App\Http\Controllers\Admin\UserManagement\RolesController;
use App\Http\Controllers\Admin\UserManagement\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\Settings\SmsSettingsController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';
Route::get('/', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified']);

Route::get('admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::get('/employee-register', [CandidateRegisterController::class, 'showRegistrationForm'])->name('employee.register');
Route::post('/employee-register', [CandidateRegisterController::class, 'register'])->name('employee.store');
Route::get('/employee-registration-complete', [CandidateRegisterController::class, 'candidate_registration_complete'])->name('employee-registration-complete');

Route::group(['middleware' => ['auth', 'verified'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('employees/search', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('employees/salary-info', [EmployeeController::class, 'salary_info'])->name('employees.salary-info');
    Route::post('get-details', [EmployeeController::class, 'getDetails'])->name('employee-salary.getDetails');
        Route::post('save', [EmployeeController::class, 'saveSalary'])->name('employee-salary.save');
    Route::resource('employees', EmployeeController::class);

    Route::resource('departments', DepartmentController::class)->except(['show'])->names('departments');
    Route::resource('designations', DesignationController::class)->except(['show'])->names('designations');

    Route::get('general-settings', [GenerelSettingController::class, 'index'])->name('general-settings.index');
    Route::post('general-settings', [GenerelSettingController::class, 'update'])->name('general-settings.update');

    Route::resource('branches', BranchController::class)->except(['show'])->names('branches');
    Route::resource('roles', RolesController::class)->except(['show'])->names('roles');

    Route::resource('users', UserController::class)->except(['show'])->names('users');
    Route::get('users/employee/{id}', [UserController::class, 'getEmployeeDetails'])->name('users.employee.details');

    Route::resource('parts', PartsController::class)->except(['show'])->names('parts');
    Route::resource('services', ServiceController::class)->except(['show'])->names('services');
    Route::resource('units', UnitController::class)->except(['show'])->names('units');
    Route::resource('sizes', SizeController::class)->except(['show'])->names('sizes');

    Route::resource('customers', CustomerController::class);
    Route::get('customers-search', [CustomerController::class, 'customer_search'])->name('customers.search');
    Route::get('admin/customers/form', [CustomerController::class, 'getForm'])->name('customers.form');

    Route::resource('suppliers', SupplierController::class);
    Route::get('admin/suppliers/form', [SupplierController::class, 'getForm'])->name('suppliers.form');



    Route::get('job-quotations/create', [JobBookController::class, 'quotationCreate'])->name('job-quotations.create');
    Route::get('jobs/search', [JobBookController::class, 'searchJobs'])->name('jobs.search');
    Route::get('jobs/get-details/{id}', [JobBookController::class, 'getJobDetails'])->name('jobs.get-details');
    Route::post('job-quotations/store', [JobBookController::class, 'storeQuotation'])->name('job-quotations.store');

    Route::get('job-invoices/create', [JobBookController::class, 'invoiceCreate'])->name('job-invoices.create');
    Route::post('job-invoices/store', [JobBookController::class, 'storeInvoice'])->name('job-invoices.store');
    Route::get('jobs/get-invoice-details/{id}', [JobBookController::class, 'getInvoiceDetails'])->name('jobs.get-invoice-details');
    Route::post('job-books/print', [JobBookController::class, 'print'])->name('job-books.print');

    Route::get('job-books/get-payment-details/{id}', [JobBookController::class, 'getPaymentDetails']);
    Route::get('job-books/get-payment-history/{typeId}', [JobBookController::class, 'getPaymentHistory']);
    Route::get('job-books/get-payment/{paymentId}', [JobBookController::class, 'getPayment']);
    Route::post('job-books/process-payment', [JobBookController::class, 'processPayment']);
    Route::post('job-books/update-payment', [JobBookController::class, 'updatePayment']);  // POST use korchi
    Route::delete('job-books/delete-payment', [JobBookController::class, 'deletePayment']);
    Route::resource('job-books', JobBookController::class);

    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show'])->names('expense-categories');

    Route::get('expenses/get-payment-details/{id}', [ExpenseController::class, 'getPaymentDetails']);
    Route::get('expenses/get-payment-history/{typeId}', [ExpenseController::class, 'getPaymentHistory']);
    Route::get('expenses/get-payment/{paymentId}', [ExpenseController::class, 'getPayment']);
    Route::post('expenses/process-payment', [ExpenseController::class, 'processPayment']);
    Route::post('expenses/update-payment', [ExpenseController::class, 'updatePayment']);
    Route::delete('expenses/delete-payment', [ExpenseController::class, 'deletePayment']);
    Route::post('expenses/print', [ExpenseController::class, 'printExpense'])->name('expenses.print');

    // Resource routes পরে রাখো
    Route::resource('expenses', ExpenseController::class);

    Route::get('payments/customer-payment/create', [PaymentController::class, 'customerPaymentCreate'])->name('payments.customer-payment.create');
    Route::get('payments/get-customer-jobs', [PaymentController::class, 'getCustomerJobs'])->name('payments.get-customer-jobs');
    Route::post('payments/store-customer-payment', [PaymentController::class, 'storeCustomerPayment'])->name('payments.store-customer-payment');
    Route::get('payments/print-receipt/{id}', [PaymentController::class, 'printReceipt'])->name('payments.print-receipt');
    Route::resource('payments', PaymentController::class);


    Route::get('sms/settings', [SmsSettingsController::class, 'index'])->name('sms.settings');
    Route::post('sms/settings/update', [SmsSettingsController::class, 'update'])->name('sms.settings.update');
});

Route::get('admin/sms/test', function() {
    $result = \App\Helpers\SmsHelper::send('01643734728', 'Test SMS from Ideal Engineering', 'Test Campaign');
    dd($result);
});
