<?php

use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Middleware\YoungerThanEightTeen;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home page
Route::get('/', [DashboardController::class, 'welcome'])->name('home');

// Legal routes
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/tos', function () {
        return Inertia::render('legal/Tos');
    })->name('tos');

    Route::get('/privacy', function () {
        return Inertia::render('legal/Privacy');
    })->name('privacy');

    Route::get('/cookie', function () {
        return Inertia::render('legal/Cookie');
    })->name('cookie');
});

// Games routes
Route::resource('/games', GameController::class);

// Event routes
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');

    // Auth required event routes
    Route::middleware('auth')->group(function () {
        Route::post('/{event}/signup', [EventController::class, 'signup'])->name('signup');
        Route::delete('/{event}/signup', [EventController::class, 'removeSignup'])->name('remove-signup');
        Route::post('/{event}/join', [EventController::class, 'join'])->name('join');
    });
});

// Location routes
Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('/', [LocationController::class, 'index'])->name('index');
    Route::get('/{location}', [LocationController::class, 'show'])->name('show');
});

// Organization routes
Route::prefix('organizations')->name('organizations.')->group(function () {
    Route::get('/', [OrganizationController::class, 'index'])->name('index');
    Route::get('/{organization}', [OrganizationController::class, 'show'])->name('show');
});

// Contact form route
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Dashboard route
Route::middleware(['auth', 'verified', YoungerThanEightTeen::class])
    ->get('dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Include other route files
require __DIR__ . '/settings.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';

// Debug routes - commented out for production
// Uncomment for debugging purposes only
/*
Route::prefix('debug')->group(function () {
    Route::get('/password-reset', [\App\Http\Controllers\DebugPasswordResetController::class, 'debug']);
    Route::get('/direct-token', [\App\Http\Controllers\TestPasswordResetController::class, 'testDirectTokenCreation']);
    Route::post('/password-reset', [\App\Http\Controllers\TestPasswordResetController::class, 'testPasswordReset']);
    Route::get('/token', [\App\Http\Controllers\TestPasswordResetController::class, 'debugToken']);
    Route::get('/summary', [\App\Http\Controllers\DebugSummaryController::class, 'summary']);
    Route::get('/fix-password-reset', [\App\Http\Controllers\FixPasswordResetController::class, 'fix']);

    // Test route for YoungerThanEightTeen middleware
    Route::get('/age-check', function() {
        // This route simulates a user born in 2009 (under 18)
        // It will trigger the YoungerThanEightTeen middleware
        return 'If you see this, the age check middleware did not redirect you.';
    })->middleware(['auth', \App\Http\Middleware\YoungerThanEightTeen::class]);
});
*/
