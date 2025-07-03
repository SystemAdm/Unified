<?php

use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PhoneController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', function () {
        return Inertia::render('admin/Index');
    })->name('index');

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
});
