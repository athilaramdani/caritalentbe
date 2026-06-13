<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TalentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\MatchmakingController;
use App\Http\Controllers\AdminController;

// Dummy login route to intercept unauthenticated redirects from Sanctum
Route::get('/login', function () {
    return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
})->name('login');

// === TEMPORARY DEBUG: hapus setelah FCM berhasil ===
Route::get('/debug/fcm-tokens', function () {
    $users = \App\Models\User::all(['id', 'email', 'role', 'fcm_token']);
    return response()->json($users);
});
Route::post('/debug/send-test-fcm', function (\Illuminate\Http\Request $req) {
    $user = \App\Models\User::find($req->user_id);
    if (!$user || !$user->fcm_token) {
        return response()->json(['error' => 'user not found or no token']);
    }
    try {
        $messaging = app('firebase.messaging');
        $msg = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $user->fcm_token)
            ->withNotification(\Kreait\Firebase\Messaging\Notification::create('Test Notif', 'Ini test dari debug endpoint'))
            ->withData(['type' => 'test']);
        $messaging->send($msg);
        return response()->json(['success' => true, 'token_used' => substr($user->fcm_token, 0, 30) . '...']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
// === END TEMPORARY DEBUG ===



Route::prefix('v1')->group(function () {

    // =====================================================
    // AUTH ROUTES (public — tidak perlu login)
    // =====================================================
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // =====================================================
    // PUBLIC ROUTES (tidak perlu login)
    // =====================================================
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/talents', [TalentController::class, 'index']);
    Route::get('/talents/my', [TalentController::class, 'myTalent'])->middleware(['auth:sanctum', 'role:talent']);
    Route::get('/talents/{id}', [TalentController::class, 'show'])->whereNumber('id');
    Route::get('/genres', [GenreController::class, 'index']);
    Route::get('/talents/{talent_id}/reviews', [ReviewController::class, 'getTalentReviews']);

    // =====================================================
    // AUTHENTICATED ROUTES (perlu login)
    // =====================================================
    Route::middleware('auth:sanctum')->group(function () {
        
        // ----- AUTH -----
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // ----- USER & PROFILE -----
        Route::put('/users/profile', [UserController::class, 'updateProfile']);
        Route::put('/users/password', [UserController::class, 'updatePassword']);
        Route::put('/users/fcm-token', [UserController::class, 'updateFcmToken']);

        // ----- EVENT MANAGEMENT (EO only) -----
        Route::get('/events/my', [EventController::class, 'myEvents'])->middleware('role:eo');
        Route::post('/events', [EventController::class, 'store'])->middleware('role:eo');
        Route::put('/events/{event}', [EventController::class, 'update'])->middleware('role:eo')->whereNumber('event');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->middleware('role:eo,admin')->whereNumber('event');

        // ----- APPLICATION (Talent only) -----
        Route::get('/applications/my', [ApplicationController::class, 'myApplications'])->middleware('role:talent');
        Route::post('/applications', [ApplicationController::class, 'store'])->middleware('role:talent');
        Route::delete('/applications/{id}', [ApplicationController::class, 'destroy'])->middleware('role:talent');

        // ----- EO MANAGEMENT OF APPLICATIONS -----
        Route::get('/events/{event_id}/applications', [ApplicationController::class, 'indexByEvent'])->middleware('role:eo');
        Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus'])->middleware('role:eo');

        // ----- INVITATION -----
        Route::post('/invitations', [InvitationController::class, 'store'])->middleware('role:eo');
        Route::get('/invitations/sent', [InvitationController::class, 'sentInvitations'])->middleware('role:eo');
        Route::get('/invitations/my', [InvitationController::class, 'myInvitations'])->middleware('role:talent');
        Route::put('/invitations/{id}/respond', [InvitationController::class, 'respond'])->middleware('role:talent');

        // ----- REVIEW -----
        Route::post('/reviews', [ReviewController::class, 'store'])->middleware('role:eo');
        Route::get('/reviews/my', [ReviewController::class, 'myReviews'])->middleware('role:talent');

        // ----- TALENT PROFILE (Talent only) -----
        Route::post('/talents', [TalentController::class, 'store'])->middleware('role:talent');
        Route::put('/talents/{id}', [TalentController::class, 'update'])->middleware('role:talent');
        Route::delete('/talents/{id}', [TalentController::class, 'destroy'])->middleware('role:talent');

        // ----- BOOKING -----
        Route::get('/bookings/my', [BookingController::class, 'getMyBookings']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::put('/bookings/{id}/complete', [BookingController::class, 'complete'])->middleware('role:eo');
        Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

        // ----- NOTIFICATION -----
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // ----- MATCHMAKING (EO only) -----
        Route::get('/events/{event_id}/recommendations', [MatchmakingController::class, 'getRecommendations'])->middleware('role:eo');

        // ----- ADMIN -----
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/users', [AdminController::class, 'getUsers']);
            Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
            Route::put('/talents/{id}/verify', [AdminController::class, 'verifyTalent']);
            Route::put('/events/{id}/moderate', [AdminController::class, 'moderateEvent']);
        });
    });

    // Public detail route HARUS paling bawah agar tidak menimpa /events/my
    Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event');
});
