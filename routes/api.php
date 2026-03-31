<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// === ROUTES ATHILA (Admin & Matchmaking) ===
Route::prefix('admin')->group(function () {
    // Route::get('/users', [App\Http\Controllers\Admin\AdminUserController::class, 'index']);
});
// Route::get('/events/{event_id}/recommendations', [App\Http\Controllers\MatchmakingController::class, 'index']);

// === ROUTES IRGI (Auth, User, Talent, Genre, Application) ===
Route::prefix('auth')->group(function () {
    // Auth routes
});
Route::prefix('users')->group(function () {
    // User profile routes
});
Route::prefix('talents')->group(function () {
    // Talent routes
});
Route::prefix('genres')->group(function () {
    // Genre routes
});
Route::prefix('applications')->group(function () {
    // Application routes
});

// === ROUTES ARFIAN (Event, Invitation, Booking, Review, Notification) ===
Route::prefix('events')->group(function () {
    // Event routes
});
Route::prefix('invitations')->group(function () {
    // Invitation routes
});
Route::prefix('bookings')->group(function () {
    // Booking routes
});
Route::prefix('reviews')->group(function () {
    // Review routes
});
Route::prefix('notifications')->group(function () {
    // Notification routes
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
