<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TalentController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MatchmakingController;
// Dummy login route to intercept unauthenticated redirects from Sanctum
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

Route::prefix('v1')->group(function () {
    // --- ROUTES ATHILA ---
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Talents & Genres public
    Route::get('/talents', [TalentController::class, 'index']);
    Route::get('/talents/{id}', [TalentController::class, 'show']);
    Route::get('/genres', [GenreController::class, 'index']);
    
    // Public Review (ARFIAN)
    Route::get('/talents/{id}/reviews', [ReviewController::class, 'getTalentReviews']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Users
        Route::put('/users/profile', [UserController::class, 'updateProfile']);
        Route::put('/users/password', [UserController::class, 'updatePassword']);

        // Talents private
        Route::post('/talents', [TalentController::class, 'store']);
        Route::put('/talents/{id}', [TalentController::class, 'update']);
        Route::delete('/talents/{id}', [TalentController::class, 'destroy']);
        Route::post('/talents/{id}/media', [TalentController::class, 'uploadMedia']);
        Route::delete('/talents/{talent_id}/media/{media_id}', [TalentController::class, 'deleteMedia']);
    });
    // --- END ROUTES ATHILA ---

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

        // --- ROUTES ARFIAN ---
        // Matchmaking
        Route::get('/events/{id}/recommendations', [MatchmakingController::class, 'getRecommendations']);
        
        // Bookings
        Route::get('/bookings/my', [BookingController::class, 'getMyBookings']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::put('/bookings/{id}/complete', [BookingController::class, 'complete']);
        Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

        // Admin
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
        Route::put('/admin/talents/{id}/verify', [AdminController::class, 'verifyTalent']);
        Route::put('/admin/events/{id}/moderate', [AdminController::class, 'moderateEvent']);
        // --- END ROUTES ARFIAN ---
    });

    // Public detail route MUST come AFTER protected /events/my group
    Route::get('/events/{event}', [EventController::class, 'show']);
});
