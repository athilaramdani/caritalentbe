<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InvitationController;

// Dummy login route to intercept unauthenticated redirects from Sanctum
Route::get('/login', function () {
    return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
})->name('login');

Route::prefix('v1')->group(function () {

    // =====================================================
    // AUTH ROUTES (public — tidak perlu login)
    // =====================================================
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Auth routes yang butuh login
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // =====================================================
    // PUBLIC ROUTES (tidak perlu login)
    // =====================================================
    Route::get('/events', [EventController::class, 'index']);

    // =====================================================
    // AUTHENTICATED ROUTES (perlu login)
    // =====================================================
    Route::middleware('auth:sanctum')->group(function () {

        // ----- EVENT MANAGEMENT (EO only) -----
        // Pembuatan & Manajemen Event → hanya EO
        // PENTING: /events/my harus SEBELUM /events/{event}
        Route::get('/events/my', [EventController::class, 'myEvents'])
            ->middleware('role:eo');
        Route::post('/events', [EventController::class, 'store'])
            ->middleware('role:eo');
        Route::put('/events/{event}', [EventController::class, 'update'])
            ->middleware('role:eo');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])
            ->middleware('role:eo,admin');

        // ----- APPLICATION (Talent only) -----
        // Melamar & Menerima Undangan → hanya Talent
        Route::get('/applications/my', [ApplicationController::class, 'myApplications'])
            ->middleware('role:talent');
        Route::post('/applications', [ApplicationController::class, 'store'])
            ->middleware('role:talent');
        Route::delete('/applications/{id}', [ApplicationController::class, 'destroy'])
            ->middleware('role:talent');

        // EO mengatur pelamar di event-nya
        Route::get('/events/{event_id}/applications', [ApplicationController::class, 'indexByEvent'])
            ->middleware('role:eo');
        Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus'])
            ->middleware('role:eo');

        // ----- INVITATION -----
        // EO nembak Talent
        Route::post('/invitations', [InvitationController::class, 'store'])
            ->middleware('role:eo');
        // Talent lihat & balas undangan
        Route::get('/invitations/my', [InvitationController::class, 'myInvitations'])
            ->middleware('role:talent');
        Route::put('/invitations/{id}/respond', [InvitationController::class, 'respond'])
            ->middleware('role:talent');
    });

    // Public detail route HARUS setelah grup protected /events/my
    Route::get('/events/{event}', [EventController::class, 'show']);
});
