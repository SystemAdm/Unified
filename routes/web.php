<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\WishlistController;
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

// Wishlist routes
Route::resource('wishlists', WishlistController::class)->only(['index', 'show']);

// Games routes
Route::resource('games', GameController::class)->only(['index', 'show']);

// News routes
Route::resource('news', NewsController::class)->only(['index', 'show']);

// Announcement routes
Route::resource('announcements', AnnouncementController::class)->only(['index', 'show']);

// Event routes
Route::prefix('events')->name('events.')->group(function () {
    // Public event routes
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
Route::resource('locations', LocationController::class)->only(['index', 'show']);

// Organization routes
Route::resource('organizations', OrganizationController::class)->only(['index', 'show']);

// Console routes
Route::resource('consoles', ConsoleController::class)->only(['index']);

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
