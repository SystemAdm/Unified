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
            $action = $event->action;
            $data = $event->data;

            // Format the date as dd/MM/YYYY
            $eventDate = $eventModel->start_date->format('d/m/Y');

            // Determine endpoint and payload based on action
            $endpoint = $this->getEndpoint($action);
            $payload = $this->getPayload($action, $eventModel, $eventDate, $data);

            // Send request to Discord bot API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key' => env('DISCORD_DISCO_API_KEY'),
            ])->post(env('DISCORD_DISCO_HOST_URI') . $endpoint, $payload);

            // Log the response
            if ($response->successful()) {
                Log::info("Event {$action} sent to Discord successfully", [
                    'event_id' => $eventModel->id,
                    'action' => $action,
                    'response' => $response->json()
                ]);
            } else {
                Log::error("Failed to send event {$action} to Discord", [
                    'event_id' => $eventModel->id,
                    'action' => $action,
                    'error' => $response->json()
                ]);

                // If needed, we can retry or handle the error differently
                $this->fail($response->json());
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending event to Discord', [
                'event_id' => $event->event->id ?? 'unknown',
                'action' => $event->action ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $this->fail($e);
        }
    }

    /**
     * Get the Discord API endpoint based on action.
     */
    private function getEndpoint(string $action): string
    {
        return match ($action) {
            'create' => 'create-event',
            'cancel' => 'cancel-event',
            'delete' => 'delete-event',
            'update-restriction' => 'update-event-restriction',
            'update' => 'update-event',
            default => 'create-event',
        };
    }

    /**
     * Get the payload for Discord API based on action.
     */
    private function getPayload(string $action, $eventModel, string $eventDate, array $data): array
    {
        return match ($action) {
            'create' => [
                'event_date' => $eventDate,
                'custom_title' => $eventModel->title,
                'restricted' => $eventModel->restriction ?? 'everyone',
            ],
            'cancel' => [
                'event_date' => $eventDate,
            ],
            'delete' => [
                'event_date' => $eventDate,
            ],
            'update-restriction' => [
                'event_date' => $eventDate,
                'restriction' => $eventModel->restriction ?? 'everyone',
            ],
            'update' => [
                'event_date' => $eventDate,
                'new_title' => $eventModel->title,
                'new_date' => $data['new_date'] ?? $eventDate,
            ],
            default => [
                'event_date' => $eventDate,
                'custom_title' => $eventModel->title,
                'restricted' => $eventModel->restriction ?? 'everyone',
            ],
        };
    }
}
