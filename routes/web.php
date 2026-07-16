<?php

use App\Http\Controllers\Donor\DonorProfileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ──────────────────────────────────────────
    // Donor Profile Completion & OTP Verification
    // ──────────────────────────────────────────
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

require __DIR__.'/auth.php';
