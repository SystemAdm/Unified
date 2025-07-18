<?php

use App\Http\Controllers\Api\DiscordBotController;
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
