<?php

use App\Http\Controllers\Donor\DonorProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicControllers\BloodRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Public — Rate-Limited Routes (throttle:10,1)
|--------------------------------------------------------------------------
| Applied to public request-posting and search routes to prevent abuse.
| 10 requests per minute per IP.
*/

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/blood-requests/create', [BloodRequestController::class, 'create'])
        ->name('blood-requests.create');
    Route::post('/blood-requests', [BloodRequestController::class, 'store'])
        ->name('blood-requests.store');
    Route::get('/blood-requests/{bloodRequest}/success', [BloodRequestController::class, 'success'])
        ->name('blood-requests.success');
    // Future: GET /search (public donor search)
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // ── Breeze Profile Management ────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Donor Profile Completion & OTP Verification ──────
    Route::get('/donor/complete-profile', [DonorProfileController::class, 'create'])
        ->name('donor.profile.create');
    Route::post('/donor/complete-profile', [DonorProfileController::class, 'store'])
        ->name('donor.profile.store');
    Route::get('/donor/verify-otp', [DonorProfileController::class, 'showOtpForm'])
        ->name('donor.otp.show');
    Route::post('/donor/verify-otp', [DonorProfileController::class, 'verifyOtp'])
        ->name('donor.otp.verify');
    Route::post('/donor/resend-otp', [DonorProfileController::class, 'resendOtp'])
        ->middleware('throttle:3,1')
        ->name('donor.otp.resend');
});

/*
|--------------------------------------------------------------------------
| Verified Donor Routes (auth + donor.verified)
|--------------------------------------------------------------------------
| Only donors who have completed their profile AND verified their phone
| can access these routes (e.g. responding to blood requests).
*/

Route::middleware(['auth', 'donor.verified'])->group(function () {
    // Future: POST /blood-requests/{id}/respond  (Prompt 10+)
    // Future: GET  /donor/dashboard              (Prompt 12)
});

/*
|--------------------------------------------------------------------------
| Admin Routes (auth + admin)
|--------------------------------------------------------------------------
| Only users with role = 'admin' can access these routes.
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Future: Admin dashboard, donor management, audit logs (Prompt 14+)
});

require __DIR__.'/auth.php';

