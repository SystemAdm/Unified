<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Event routes
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::post('/events/{event}/signup', [\App\Http\Controllers\EventController::class, 'signup'])->name('events.signup')->middleware('auth');
Route::delete('/events/{event}/signup', [\App\Http\Controllers\EventController::class, 'removeSignup'])->name('events.remove-signup')->middleware('auth');
Route::post('/events/{event}/join', [\App\Http\Controllers\EventController::class, 'join'])->name('events.join')->middleware('auth');

// Location routes
Route::get('/locations', [\App\Http\Controllers\LocationController::class, 'index'])->name('locations.index');
Route::get('/locations/{location}', [\App\Http\Controllers\LocationController::class, 'show'])->name('locations.show');

// Organization routes
Route::get('/organizations', [\App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations.index');
Route::get('/organizations/{organization}', [\App\Http\Controllers\OrganizationController::class, 'show'])->name('organizations.show');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Debug routes for password reset
Route::get('/debug-password-reset', [\App\Http\Controllers\DebugPasswordResetController::class, 'debug']);
Route::get('/test-direct-token', [\App\Http\Controllers\TestPasswordResetController::class, 'testDirectTokenCreation']);
Route::post('/test-password-reset', [\App\Http\Controllers\TestPasswordResetController::class, 'testPasswordReset']);
Route::get('/debug-token', [\App\Http\Controllers\TestPasswordResetController::class, 'debugToken']);
Route::get('/debug-summary', [\App\Http\Controllers\DebugSummaryController::class, 'summary']);
Route::get('/fix-password-reset', [\App\Http\Controllers\FixPasswordResetController::class, 'fix']);

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
