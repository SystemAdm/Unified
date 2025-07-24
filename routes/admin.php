<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\GuardianVerificationController;
use App\Http\Controllers\Admin\PhoneController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', function () {
        return Inertia::render('admin/Index');
    })->name('index');

    Route::resource('games', GameController::class);

    // Banner management routes
    Route::resource('banners', BannerController::class);

    // Announcement management routes
    Route::resource('announcements', AnnouncementController::class);

    // News management routes
    Route::resource('news', NewsController::class);

    // Organization management routes
    Route::resource('organizations', OrganizationController::class);

    // User management routes
    Route::resource('users', UserController::class);

    // Role management routes
    Route::resource('roles', RoleController::class);

    // Permission management routes
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);

    // Event management routes
    Route::resource('events', EventController::class);

    // Additional event management routes
    Route::patch('events/{event}/force-start-signup', [EventController::class, 'forceStartSignup'])->name('events.force-start-signup');
    Route::patch('events/{event}/force-end-signup', [EventController::class, 'forceEndSignup'])->name('events.force-end-signup');
    Route::patch('events/{event}/force-start-event', [EventController::class, 'forceStartEvent'])->name('events.force-start-event');
    Route::patch('events/{event}/force-end-event', [EventController::class, 'forceEndEvent'])->name('events.force-end-event');
    Route::patch('events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

    // Event user management routes
    Route::post('events/{event}/copy-to-registered/{user}', [EventController::class, 'copyToRegistered'])->name('events.copy-to-registered');
    Route::delete('events/{event}/remove-from-all/{user}', [EventController::class, 'removeFromAll'])->name('events.remove-from-all');
    Route::post('events/{event}/copy-to-attending/{user}', [EventController::class, 'copyToAttending'])->name('events.copy-to-attending');
    Route::delete('events/{event}/remove-from-registered-attending/{user}', [EventController::class, 'removeFromRegisteredAttending'])->name('events.remove-from-registered-attending');
    Route::delete('events/{event}/remove-from-attending/{user}', [EventController::class, 'removeFromAttending'])->name('events.remove-from-attending');

    // Event user list routes
    Route::get('events/{event}/users/registered', [EventController::class, 'showRegisteredUsers'])->name('events.users.registered');
    Route::get('events/{event}/users/visited', [EventController::class, 'showVisitedUsers'])->name('events.users.visited');
    Route::get('events/{event}/users/inside', [EventController::class, 'showInsideUsers'])->name('events.users.inside');
    Route::get('events/{event}/users', [EventController::class, 'showAllUsers'])->name('events.users.all');

    // Encrypted text validation route
    Route::get('events/{event}/validate-text', [EventController::class, 'showValidateText'])->name('events.validate-text');
    Route::post('events/{event}/validate-text', [EventController::class, 'validateText'])->name('events.validate-text.submit');

    // Phone management routes
    Route::resource('phones', PhoneController::class);
    Route::post('phones/{phone}/attach-user', [PhoneController::class, 'attachUser'])->name('phones.attach-user');
    Route::delete('phones/{phone}/detach-user', [PhoneController::class, 'detachUser'])->name('phones.detach-user');
    Route::patch('phones/{phone}/set-primary', [PhoneController::class, 'setPrimary'])->name('phones.set-primary');
    Route::patch('phones/{phone}/set-verified', [PhoneController::class, 'setVerified'])->name('phones.set-verified');

    // Email management routes
    Route::resource('emails', EmailController::class);
    Route::post('emails/{email}/attach-user', [EmailController::class, 'attachUser'])->name('emails.attach-user');
    Route::delete('emails/{email}/detach-user', [EmailController::class, 'detachUser'])->name('emails.detach-user');
    Route::patch('emails/{email}/set-primary', [EmailController::class, 'setPrimary'])->name('emails.set-primary');
    Route::patch('emails/{email}/set-verified', [EmailController::class, 'setVerified'])->name('emails.set-verified');

    // Location management routes
    Route::resource('locations', LocationController::class);

    // Guardian verification routes
    Route::get('guardian-verification', [GuardianVerificationController::class, 'index'])->name('guardian-verification.index');
    Route::post('guardian-verification/{id}/verify', [GuardianVerificationController::class, 'verify'])->name('guardian-verification.verify');
});
