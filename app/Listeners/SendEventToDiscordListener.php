<?php

namespace App\Listeners;

use App\Events\SendEventToDiscord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendEventToDiscordListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendEventToDiscord $event): void
    {
        try {
            $eventModel = $event->event;

            // Format the date as dd/MM/YYYY
            $eventDate = $eventModel->start_date->format('d/m/Y');

            // Send request to Discord bot API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key' => env('DISCORD_DISCO_API_KEY'),
            ])->post(env('DISCORD_DISCO_HOST_URI'), [
                'event_date' => $eventDate,
                'custom_title' => $eventModel->title,
                'restricted' => $eventModel->restriction ?? 'everyone', // Options: 'everyone', 'members', 'crew'
            ]);

            // Log the response
            if ($response->successful()) {
                Log::info('Event sent to Discord successfully', [
                    'event_id' => $eventModel->id,
                    'response' => $response->json()
                ]);
            } else {
                Log::error('Failed to send event to Discord', [
                    'event_id' => $eventModel->id,
                    'error' => $response->json()
                ]);

                // If needed, we can retry or handle the error differently
                $this->fail($response->json());
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending event to Discord', [
                'event_id' => $event->event->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $this->fail($e);
        }
    }
}
