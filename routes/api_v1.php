<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\CustomerNumberController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RegistrationController;
use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Routes for the customer-facing mobile PWA API.
| All routes are prefixed with /api/v1 and use web middleware
| for Sanctum SPA cookie-based authentication.
|
*/

// Guest routes (no auth required)
Route::middleware('throttle:guest')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('api.v1.auth.login');

    // Password Reset (deferred post-MVP)
    // Route::post('/auth/forgot-password', ...)->name('api.v1.auth.forgot-password');
    // Route::post('/auth/reset-password', ...)->name('api.v1.auth.reset-password');
});

// Authenticated customer routes
Route::middleware(['auth:sanctum', 'customer'])->group(function () {

    Route::middleware('throttle:api')->group(function () {
        // Auth (authenticated)
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');

        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('api.v1.profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('api.v1.profile.update');
        Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('api.v1.profile.password');

        // Master Data
        Route::get('/master/programs', [MasterDataController::class, 'programs'])->name('api.v1.master.programs');
        Route::get('/master/provinces', [MasterDataController::class, 'provinces'])->name('api.v1.master.provinces');
        Route::get('/master/regencies/{province}', [MasterDataController::class, 'regencies'])->whereNumber('province')->name('api.v1.master.regencies');
        Route::get('/master/districts/{regency}', [MasterDataController::class, 'districts'])->whereNumber('regency')->name('api.v1.master.districts');
        Route::get('/master/villages/{district}', [MasterDataController::class, 'villages'])->whereNumber('district')->name('api.v1.master.villages');
        Route::get('/master/complaint-types', [MasterDataController::class, 'complaintTypes'])->name('api.v1.master.complaint-types');

        // Customer Numbers
        Route::post('/customer-numbers/verify', [CustomerNumberController::class, 'verify'])->name('api.v1.customer-numbers.verify');
        Route::post('/customer-numbers/confirm', [CustomerNumberController::class, 'confirm'])->name('api.v1.customer-numbers.confirm');
        Route::get('/customer-numbers', [CustomerNumberController::class, 'index'])->name('api.v1.customer-numbers.index');
        Route::get('/customer-numbers/{no}/billing', [CustomerNumberController::class, 'billing'])->name('api.v1.customer-numbers.billing');
        Route::delete('/customer-numbers/{no}', [CustomerNumberController::class, 'destroy'])->name('api.v1.customer-numbers.destroy');

        // SR Registrations
        Route::post('/registrations', [RegistrationController::class, 'store'])->name('api.v1.registrations.store');
        Route::get('/registrations', [RegistrationController::class, 'index'])->name('api.v1.registrations.index');
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('api.v1.registrations.show');

        // Complaints
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('api.v1.complaints.store');
        Route::get('/complaints', [ComplaintController::class, 'index'])->name('api.v1.complaints.index');
        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('api.v1.complaints.show');
        Route::get('/complaints/{complaint}/timeline', [ComplaintController::class, 'timeline'])->name('api.v1.complaints.timeline');
    });

    // File Upload (separate rate limit)
    Route::post('/uploads', [UploadController::class, 'store'])->middleware('throttle:upload')->name('api.v1.uploads.store');
});
