<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ConsoleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\GuardianVerificationController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PhoneController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // Basic resource management routes
    Route::resource('games', GameController::class);
    Route::resource('consoles', ConsoleController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('announcements', AnnouncementController::class);
    Route::resource('news', NewsController::class);
    Route::resource('organizations', OrganizationController::class);
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);
    Route::resource('locations', LocationController::class);

    // Event management routes
    Route::resource('events', EventController::class);

    // Additional event management routes
    Route::prefix('events')->name('events.')->group(function () {
        // Event status management
        Route::patch('{event}/force-start-signup', [EventController::class, 'forceStartSignup'])->name('force-start-signup');
        Route::patch('{event}/force-end-signup', [EventController::class, 'forceEndSignup'])->name('force-end-signup');
        Route::patch('{event}/force-start-event', [EventController::class, 'forceStartEvent'])->name('force-start-event');
        Route::patch('{event}/force-end-event', [EventController::class, 'forceEndEvent'])->name('force-end-event');
        Route::patch('{event}/cancel', [EventController::class, 'cancel'])->name('cancel');

        // Event user management
        Route::prefix('{event}/users')->name('users.')->group(function () {
            // User list routes
            Route::get('/', [EventController::class, 'showAllUsers'])->name('all');
            Route::get('registered', [EventController::class, 'showRegisteredUsers'])->name('registered');
            Route::get('visited', [EventController::class, 'showVisitedUsers'])->name('visited');
            Route::get('inside', [EventController::class, 'showInsideUsers'])->name('inside');

            // User status management
            Route::post('copy-to-registered/{user}', [EventController::class, 'copyToRegistered'])->name('copy-to-registered');
            Route::delete('remove-from-all/{user}', [EventController::class, 'removeFromAll'])->name('remove-from-all');
            Route::post('copy-to-attending/{user}', [EventController::class, 'copyToAttending'])->name('copy-to-attending');
            Route::delete('remove-from-registered-attending/{user}', [EventController::class, 'removeFromRegisteredAttending'])->name('remove-from-registered-attending');
            Route::delete('remove-from-attending/{user}', [EventController::class, 'removeFromAttending'])->name('remove-from-attending');
            Route::post('copy-to-inside/{user}', [EventController::class, 'copyToInside'])->name('copy-to-inside');
            Route::delete('remove-from-inside/{user}', [EventController::class, 'removeFromInside'])->name('remove-from-inside');
        });

        // Encrypted text validation
        Route::get('{event}/validate-text', [EventController::class, 'showValidateText'])->name('validate-text');
        Route::post('{event}/validate-text', [EventController::class, 'validateText'])->name('validate-text.submit');
    });

    // Phone management routes
    Route::resource('phones', PhoneController::class);
    Route::prefix('phones')->name('phones.')->group(function () {
        Route::post('{phone}/attach-user', [PhoneController::class, 'attachUser'])->name('attach-user');
        Route::delete('{phone}/detach-user', [PhoneController::class, 'detachUser'])->name('detach-user');
        Route::patch('{phone}/set-primary', [PhoneController::class, 'setPrimary'])->name('set-primary');
        Route::patch('{phone}/set-verified', [PhoneController::class, 'setVerified'])->name('set-verified');
    });

    // Email management routes
    Route::resource('emails', EmailController::class);
    Route::prefix('emails')->name('emails.')->group(function () {
        Route::post('{email}/attach-user', [EmailController::class, 'attachUser'])->name('attach-user');
        Route::delete('{email}/detach-user', [EmailController::class, 'detachUser'])->name('detach-user');
        Route::patch('{email}/set-primary', [EmailController::class, 'setPrimary'])->name('set-primary');
        Route::patch('{email}/set-verified', [EmailController::class, 'setVerified'])->name('set-verified');
    });

    // Wishlist management routes
    Route::resource('wishlists', WishlistController::class)->names([
        'index' => 'wishlist.index',
        'create' => 'wishlist.create',
        'store' => 'wishlist.store',
        'show' => 'wishlist.show',
        'edit' => 'wishlist.edit',
        'update' => 'wishlist.update',
        'destroy' => 'wishlist.destroy',
    ]);
    Route::prefix('wishlists')->name('wishlist.')->group(function () {
        Route::post('{wishlist}/record-payment', [WishlistController::class, 'recordPayment'])->name('record-payment');
        Route::get('get-users', [WishlistController::class, 'getUsers'])->name('get-users');
    });

    // Guardian verification routes
    Route::prefix('guardian-verification')->name('guardian-verification.')->group(function () {
        Route::get('/', [GuardianVerificationController::class, 'index'])->name('index');
        Route::post('{id}/verify', [GuardianVerificationController::class, 'verify'])->name('verify');
    });
});
