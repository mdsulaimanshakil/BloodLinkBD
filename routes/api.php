<?php

use App\Http\Controllers\Api\V1\BloodRequestApiController;
use App\Http\Controllers\Api\V1\DonorResponseApiController;
use App\Http\Controllers\Api\V1\DonorSearchApiController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Prompt 18: Versioned REST API — /api/v1
|--------------------------------------------------------------------------
| Authentication: Sanctum token (Bearer token in Authorization header).
| All responses use API Resource classes — shape is stable if schema shifts.
|
| Token flow:
|   POST   /api/v1/tokens             → issue a token (login)
|   DELETE /api/v1/tokens             → revoke token (logout)
|   GET    /api/v1/user               → authenticated user info
|
| Public endpoints (no token required):
|   GET    /api/v1/donors             → donor search (phone masked unless token + verified)
|   GET    /api/v1/requests           → live request feed
|   GET    /api/v1/requests/{id}      → request detail
|
| Authenticated endpoints (Bearer token required):
|   POST   /api/v1/requests           → create a blood request
|   POST   /api/v1/requests/{id}/respond → "I Can Help" response
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Token: Login (public) ───────────────────────────────────────────────
    Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.store');

    // ── Token: Logout + Current User (requires token) ───────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/tokens', [TokenController::class, 'destroy'])->name('tokens.destroy');
        Route::get('/user',      [TokenController::class, 'me'])->name('user');
    });

    // ── Public: Donor Search ────────────────────────────────────────────────
    // Uses optional Sanctum token via statefulApi — if a valid token is sent,
    // phone numbers are unmasked for eligible callers. No token = masked phones.
    Route::get('/donors', [DonorSearchApiController::class, 'index'])->name('donors.index');

    // ── Public: Request Feed & Detail ───────────────────────────────────────
    Route::get('/requests',                  [BloodRequestApiController::class, 'index'])->name('requests.index');
    Route::get('/requests/{bloodRequest}',   [BloodRequestApiController::class, 'show'])->name('requests.show');

    // ── Authenticated: Create Request ───────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/requests', [BloodRequestApiController::class, 'store'])->name('requests.store');
        Route::post('/requests/{bloodRequest}/respond', [DonorResponseApiController::class, 'store'])
            ->name('requests.respond');
    });
});
