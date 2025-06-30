<?php

use App\Http\Controllers\Settings\EmailController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // Email settings page
    Route::get('settings/email', [EmailController::class, 'edit'])->name('email.edit');

    // Email management routes
    Route::post('settings/emails', [EmailController::class, 'store'])->name('emails.store');
    Route::patch('settings/emails/{email}/primary', [EmailController::class, 'setPrimary'])->name('emails.primary');
    Route::patch('settings/emails/{email}', [EmailController::class, 'update'])->name('emails.update');
    Route::post('settings/emails/{email}/verify', [EmailController::class, 'sendVerification'])->name('emails.send-verification');
    Route::get('settings/emails/verify/{id}/{email_id}', [EmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('emails.verify');
    Route::delete('settings/emails/{email}', [EmailController::class, 'destroy'])->name('emails.destroy');
});
