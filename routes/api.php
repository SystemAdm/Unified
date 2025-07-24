<?php

use App\Http\Controllers\Api\DiscordBotController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Discord Bot API routes
Route::middleware(['api.key'])->group(function () {
    Route::post('/create-event', [DiscordBotController::class, 'createEvent']);
    Route::post('/cancel-event', [DiscordBotController::class, 'cancelEvent']);
    Route::post('/delete-event', [DiscordBotController::class, 'deleteEvent']);
    Route::post('/update-event-restriction', [DiscordBotController::class, 'updateEventRestriction']);
    Route::post('/update-event', [DiscordBotController::class, 'updateEvent']);
});

// Public API routes for Welcome page
Route::get('/banners/active', [BannerController::class, 'getActiveBanners']);
Route::get('/announcements/active', [AnnouncementController::class, 'getActiveAnnouncements']);
Route::get('/news/latest', [NewsController::class, 'getLatestNews']);
Route::get('/news/{id}', [NewsController::class, 'show']);
