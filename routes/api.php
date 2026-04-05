<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InvitationController;

// Dummy login route to intercept unauthenticated redirects from Sanctum
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/events', [EventController::class, 'index']);

    // Protected routes - MUST be declared BEFORE /events/{event} to avoid route conflict
    Route::middleware('auth:sanctum')->group(function () {
        // Events - /events/my MUST come before /events/{event}
        Route::get('/events/my', [EventController::class, 'myEvents']);
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);

        // Applications
        Route::get('/applications/my', [ApplicationController::class, 'myApplications']);
        Route::post('/applications', [ApplicationController::class, 'store']);
        Route::get('/events/{event_id}/applications', [ApplicationController::class, 'indexByEvent']);
        Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
        Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);

        // Invitations
        Route::post('/invitations', [InvitationController::class, 'store']);
        Route::get('/invitations/my', [InvitationController::class, 'myInvitations']);
        Route::put('/invitations/{id}/respond', [InvitationController::class, 'respond']);
    });

    // Public detail route MUST come AFTER protected /events/my group
    Route::get('/events/{event}', [EventController::class, 'show']);
});
