<?php

use App\Http\Controllers\Admin\JobPostController;
use App\Http\Controllers\Auth\CandidateRegisterController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/candidate-register', [CandidateRegisterController::class, 'showRegistrationForm'])->name('candidate.register');
Route::post('/candidate-register', [CandidateRegisterController::class, 'register'])->name('candidate.store');
Route::get('/candidate-registration-complete', [CandidateRegisterController::class, 'candidate_registration_complete'])->name('candidate-registration-complete');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('job-posts', JobPostController::class);
    Route::resource('candidates', CandidateController::class);
    Route::resource('departments', DepartmentController::class)->except(['show'])->names('departments');;

    Route::get('candidate-approve/{id}', [CandidateController::class,'approve'])->name('candidates.approve');
});
