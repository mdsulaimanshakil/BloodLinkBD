<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\HospitalController as AdminHospitalController;
use App\Http\Controllers\Donor\DonorDashboardController;
use App\Http\Controllers\Donor\DonorProfileController;
use App\Http\Controllers\Donor\DonorResponseController;
use App\Http\Controllers\LanguageSwitcherController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HospitalDirectoryController;
use App\Http\Controllers\PublicControllers\BloodRequestController;
use App\Http\Controllers\PublicControllers\DonorSearchController;
use App\Http\Controllers\PublicControllers\LiveFeedController;
use App\Http\Controllers\PublicControllers\PublicStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Prompt 21: Language Switcher
Route::post('/language', [LanguageSwitcherController::class, 'switch'])->name('language.switch');

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
    Route::get('/blood-requests/{bloodRequest}', [BloodRequestController::class, 'show'])
        ->name('blood-requests.show');
    Route::get('/blood-requests/{bloodRequest}/success', [BloodRequestController::class, 'success'])
        ->name('blood-requests.success');

    // Prompt 11: Public donor search (no login required)
    Route::get('/donors', [DonorSearchController::class, 'index'])
        ->name('donor-search');

    // Prompt 12: Live request feed
    Route::get('/live-feed', [LiveFeedController::class, 'index'])
        ->name('live-feed');
    Route::get('/live-feed/poll', [LiveFeedController::class, 'poll'])
        ->name('live-feed.poll');

    // Prompt 15: Public hospital & blood bank directory
    Route::get('/hospitals', [HospitalDirectoryController::class, 'index'])
        ->name('hospitals.index');
    Route::get('/hospitals/{hospital}', [HospitalDirectoryController::class, 'show'])
        ->name('hospitals.show');

    // Prompt 17: Public stats page (cached 10 min)
    Route::get('/stats', [PublicStatsController::class, 'index'])
        ->name('stats');
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

    // Prompt 14: Post-donation feedback (requester submits rating for a donation)
    Route::get('/donation-history/{donationHistory}/feedback', [DonorDashboardController::class, 'showFeedbackForm'])
        ->name('feedback.show');
    Route::post('/donation-history/{donationHistory}/feedback', [DonorDashboardController::class, 'submitFeedback'])
        ->name('feedback.submit');
});

/*
|--------------------------------------------------------------------------
| Verified Donor Routes (auth + donor.verified)
|--------------------------------------------------------------------------
| Only donors who have completed their profile AND verified their phone
| can access these routes (e.g. responding to blood requests).
*/

Route::middleware(['auth', 'donor.verified'])->group(function () {
    // Prompt 13: "I Can Help" – donor responds to a blood request
    Route::post('/blood-requests/{bloodRequest}/respond', [DonorResponseController::class, 'store'])
        ->name('blood-requests.respond');

    // Prompt 14: Donor dashboard & donation history
    Route::get('/donor/dashboard', [DonorDashboardController::class, 'index'])
        ->name('donor.dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (auth + admin)
|--------------------------------------------------------------------------
| Only users with role = 'admin' can access these routes.
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Prompt 16: Admin dashboard — stats overview
    Route::get('/', [AdminDashboardController::class, 'index'])
        ->name('dashboard');

    // Prompt 16: Donor verification queue
    Route::get('/donors/pending', [AdminDashboardController::class, 'pendingDonors'])
        ->name('donors.pending');
    Route::post('/donors/{donorProfile}/verify', [AdminDashboardController::class, 'verifyDonor'])
        ->name('donors.verify');
    Route::post('/donors/{donorProfile}/reject', [AdminDashboardController::class, 'rejectDonor'])
        ->name('donors.reject');

    // Prompt 16: Blood request management
    Route::get('/requests', [AdminDashboardController::class, 'requests'])
        ->name('requests.index');
    Route::post('/requests/{bloodRequest}/remove', [AdminDashboardController::class, 'removeRequest'])
        ->name('requests.remove');
    Route::post('/requests/{bloodRequest}/restore', [AdminDashboardController::class, 'restoreRequest'])
        ->name('requests.restore');

    // Prompt 16: Audit log viewer
    Route::get('/audit-log', [AdminDashboardController::class, 'auditLog'])
        ->name('audit-log');

    // Prompt 15: Admin CRUD for hospitals & blood banks
    Route::resource('hospitals', AdminHospitalController::class);
});


require __DIR__.'/auth.php';
