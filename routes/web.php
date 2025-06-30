<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

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
