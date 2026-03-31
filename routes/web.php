<?php

use App\Http\Controllers\Auth\CandidateRegisterController;
use App\Http\Controllers\Admin\Hrm\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\Hrm\DesignationController;
use App\Http\Controllers\Admin\Settings\PartsController;
use App\Http\Controllers\Admin\Settings\GenerelSettingController;
use App\Http\Controllers\Admin\Settings\BranchController;
use App\Http\Controllers\Admin\Settings\RolesController;
use App\Http\Controllers\Admin\Settings\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';
Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified']);

Route::get('admin/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/employee-register', [CandidateRegisterController::class, 'showRegistrationForm'])->name('employee.register');
Route::post('/employee-register', [CandidateRegisterController::class, 'register'])->name('employee.store');
Route::get('/employee-registration-complete', [CandidateRegisterController::class, 'candidate_registration_complete'])->name('employee-registration-complete');

Route::group(['middleware' => ['auth', 'verified'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('employees/search', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('employees/salary-info', [EmployeeController::class, 'salary_info'])->name('employees.salary-info');
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
});
