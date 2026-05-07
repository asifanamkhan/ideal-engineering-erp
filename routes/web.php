<?php

use App\Http\Controllers\Admin\Contacts\CustomerController;
use App\Http\Controllers\Admin\Contacts\SupplierController;
use App\Http\Controllers\Auth\CandidateRegisterController;
use App\Http\Controllers\Admin\Hrm\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\Expense\ExpenseCategoryController;
use App\Http\Controllers\Admin\Expense\ExpenseController;
use App\Http\Controllers\Admin\Hrm\AttendanceController;
use App\Http\Controllers\Admin\Hrm\DesignationController;
use App\Http\Controllers\Admin\Hrm\HrmSettingController;
use App\Http\Controllers\Admin\Hrm\LeaveApplicationController;
use App\Http\Controllers\Admin\Hrm\LeaveTypeController;
use App\Http\Controllers\Admin\Hrm\OvertimeController;
use App\Http\Controllers\Admin\Hrm\SalaryController;
use App\Http\Controllers\Admin\Hrm\TimesheetController;
use App\Http\Controllers\Admin\Hrm\WeekendSettingController;
use App\Http\Controllers\Admin\Job\JobInvoiceController;
use App\Http\Controllers\Admin\Job\JobPartController;
use App\Http\Controllers\Admin\Job\JobQuotationController;
use App\Http\Controllers\Admin\JobBookController;
use App\Http\Controllers\Admin\Settings\PartsController;
use App\Http\Controllers\Admin\Settings\GenerelSettingController;
use App\Http\Controllers\Admin\Settings\BranchController;
use App\Http\Controllers\Admin\Settings\ServiceController;
use App\Http\Controllers\Admin\Settings\SizeController;
use App\Http\Controllers\Admin\Settings\UnitController;
use App\Http\Controllers\Admin\Settings\JobDescriptionsController;
use App\Http\Controllers\Admin\UserManagement\RolesController;
use App\Http\Controllers\Admin\UserManagement\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\Settings\InvoiceSettingController;
use App\Http\Controllers\Admin\Settings\SmsSettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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
    Route::post('save-salary', [EmployeeController::class, 'saveSalary'])->name('employee-salary.save');
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
    Route::resource('job_descriptions', JobDescriptionsController::class)->except(['show'])->names('job_descriptions');
    Route::resource('sizes', SizeController::class)->except(['show'])->names('sizes');

    Route::resource('customers', CustomerController::class);
    Route::get('customers-search', [CustomerController::class, 'customer_search'])->name('customers.search');
    Route::get('admin/customers/form', [CustomerController::class, 'getForm'])->name('customers.form');

    Route::resource('suppliers', SupplierController::class);
    Route::get('admin/suppliers/form', [SupplierController::class, 'getForm'])->name('suppliers.form');


    Route::get('jobs/search', [JobBookController::class, 'searchJobs'])->name('jobs.search');
    Route::get('jobs/get-details/{id}', [JobBookController::class, 'getJobDetails'])->name('jobs.get-details');
    Route::post('job-books/print', [JobBookController::class, 'print'])->name('job-books.print');
    Route::get('job-books/get-payment-details/{id}', [JobBookController::class, 'getPaymentDetails']);
    Route::get('job-books/get-payment-history/{typeId}', [JobBookController::class, 'getPaymentHistory']);
    Route::get('job-books/get-payment/{paymentId}', [JobBookController::class, 'getPayment']);
    Route::post('job-books/process-payment', [JobBookController::class, 'processPayment']);
    Route::post('job-books/update-payment', [JobBookController::class, 'updatePayment']);  // POST use korchi
    Route::delete('job-books/delete-payment', [JobBookController::class, 'deletePayment']);
    Route::post('job-books/change-status', [JobBookController::class, 'changeStatus'])->name('job-books.change-status');
    Route::post('job-books/print-challan', [JobBookController::class, 'printChallan'])->name('job-books.print-challan');
    Route::get('job-books/get-descriptions/{id}', [JobBookController::class, 'getDescriptions'])->name('job-books.get-descriptions');
    Route::resource('job-books', JobBookController::class);

    Route::get('job-parts', [JobPartController::class, 'index'])->name('job-parts.index');
    Route::get('job-parts/create/{job_id?}', [JobPartController::class, 'create'])->name('job-parts.create');
    Route::get('job-parts/edit/{id}', [JobPartController::class, 'edit'])->name('job-parts.edit');
    Route::get('job-parts/get-details/{id}', [JobPartController::class, 'getPartDetails']);
    Route::post('job-parts/store', [JobPartController::class, 'store'])->name('job-parts.store');
    Route::put('job-parts/update/{id}', [JobPartController::class, 'update'])->name('job-parts.update');
    Route::delete('job-parts/delete/{id}', [JobPartController::class, 'destroy'])->name('job-parts.delete');

    Route::get('job-quotations/create', [JobQuotationController::class, 'quotationCreate'])->name('job-quotations.create');
    Route::get('job-quotations', [JobQuotationController::class, 'index'])->name('job-quotations.index');
    Route::post('job-quotations/store', [JobQuotationController::class, 'storeQuotation'])->name('job-quotations.store');
    Route::post('job-quotations/convert-to-invoice', [JobQuotationController::class, 'convertToInvoice'])->name('job-quotations.convert-to-invoice');
    Route::get('job-quotations/get-details/{id}', [JobQuotationController::class, 'getDetails'])->name('job-quotations.get-details');
    Route::delete('job-quotations/delete/{id}', [JobQuotationController::class, 'destroy'])->name('job-quotations.delete');

    Route::get('job-invoices/create', [JobInvoiceController::class, 'invoiceCreate'])->name('job-invoices.create');
    Route::post('job-invoices/store', [JobInvoiceController::class, 'storeInvoice'])->name('job-invoices.store');
    Route::get('jobs/get-invoice-details/{id}', [JobInvoiceController::class, 'getInvoiceDetails'])->name('jobs.get-invoice-details');
    Route::get('job-invoices', [JobInvoiceController::class, 'index'])->name('job-invoices.index');
    Route::get('job-invoices/get-details/{id}', [JobInvoiceController::class, 'getInvoiceDetails'])->name('admin.job-invoices.get-details');
    Route::delete('job-invoices/delete/{id}', [JobInvoiceController::class, 'destroy'])->name('job-invoices.delete');

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

    Route::get('invoice-settings', [InvoiceSettingController::class, 'index'])->name('invoice-settings.index');
    Route::post('invoice-settings/update', [InvoiceSettingController::class, 'update'])->name('invoice-settings.update');

    Route::resource('salary', SalaryController::class);

});

Route::prefix('admin/hrm')->name('admin.hrm.')->group(function () {

    Route::get('settings', [HrmSettingController::class, 'index'])->name('settings.index');
    Route::post('settings/update', [HrmSettingController::class, 'update'])->name('settings.update');

    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('weekend-settings', WeekendSettingController::class);

    Route::post('leave-applications/check-balance', [LeaveApplicationController::class, 'checkBalance'])->name('leave-applications.check-balance');
    Route::post('leave-applications/approve/{id}', [LeaveApplicationController::class, 'approve'])->name('leave-applications.approve');
    Route::post('leave-applications/reject/{id}', [LeaveApplicationController::class, 'reject'])->name('leave-applications.reject');
    Route::post('leave-applications/unapprove/{id}', [LeaveApplicationController::class, 'unapprove'])->name('leave-applications.unapprove');
    Route::get('leave-applications/balance', [LeaveApplicationController::class, 'balanceIndex'])->name('leave-applications.balance');
    Route::resource('leave-applications', LeaveApplicationController::class);

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('attendance/details/{id}', [AttendanceController::class, 'getDetails'])->name('attendance.details');
    Route::get('attendance/details-report', [AttendanceController::class, 'detailsReport'])->name('attendance.details-report');
    Route::post('attendance/update-single', [AttendanceController::class, 'updateSingle'])->name('attendance.update-single');

    Route::post('salary/generate-details', [SalaryController::class, 'generateDetails'])->name('salary.generate-details');
    Route::post('salary/mark-paid/{id}', [SalaryController::class, 'markPaid'])->name('salary.mark-paid');
    Route::post('salary/get-unpaid-history', [SalaryController::class, 'getUnpaidHistory'])->name('salary.get-unpaid-history');
    Route::resource('salary', SalaryController::class);

    Route::get('overtime', [OvertimeController::class, 'index'])->name('overtime.index');
    Route::post('overtime', [OvertimeController::class, 'store'])->name('overtime.store');

    Route::get('overtime/date-wise', [OvertimeController::class, 'dateWise'])->name('overtime.date-wise');
    Route::get('overtime/employee-wise', [OvertimeController::class, 'employeeWise'])->name('overtime.employee-wise');
    Route::get('overtime/view-by-date/{date}', [OvertimeController::class, 'viewByDate'])->name('overtime.view-by-date');

    Route::post('overtime/get-date-wise-details', [OvertimeController::class, 'getDateWiseDetails'])->name('overtime.get-date-wise-details');
    Route::post('overtime/get-employee-wise-details', [OvertimeController::class, 'getEmployeeWiseDetails'])->name('overtime.get-employee-wise-details');
    Route::post('overtime/store-date-wise', [OvertimeController::class, 'storeDateWise'])->name('overtime.store-date-wise');
    Route::post('overtime/store-employee-wise', [OvertimeController::class, 'storeEmployeeWise'])->name('overtime.store-employee-wise');

    Route::post('overtime/mark-as-paid/{date}', [OvertimeController::class, 'markAsPaid'])->name('overtime.mark-as-paid');
    Route::delete('overtime/delete-by-date/{date}', [OvertimeController::class, 'deleteByDate'])->name('overtime.delete-by-date');

    Route::get('timesheet', [TimesheetController::class, 'index'])->name('timesheet.index');
    Route::post('timesheet/get-data', [TimesheetController::class, 'getData'])->name('timesheet.get-data');

});

Route::get('admin/sms/test', function() {
    $result = \App\Helpers\SmsHelper::send('01643734728', 'Test SMS from Ideal Engineering', 'Test Campaign');
    dd($result);
});
Route::get('db-migrate', function() {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'success' => true,
            'message' => 'Migration completed successfully!',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage()
        ], 500);
    }
});


Route::get('optimize-clear', function() {
    try {
        // Clear all cache
        Artisan::call('optimize:clear');

        // Individual clears (optional, included in optimize:clear)
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');

        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully!',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to clear cache: ' . $e->getMessage()
        ], 500);
    }
});
