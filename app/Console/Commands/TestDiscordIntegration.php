<?php

namespace App\Console\Commands;

use App\Events\SendEventToDiscord;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event as EventFacade;

class TestDiscordIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:discord-integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Discord integration for event notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Discord Integration...');

        // Create a test event
        $event = new Event([
            'title' => 'Test Discord Event',
            'description' => 'Testing Discord integration',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'status' => 'published',
            'restriction' => 'everyone'
        ]);

        // Set a fake ID for testing
        $event->id = 999;

        $this->info("Created test event: {$event->title}");

        // Test if we can dispatch the event
        try {
            SendEventToDiscord::dispatch($event);
            $this->info('✓ Discord event dispatched successfully');
        } catch (\Exception $e) {
            $this->error('✗ Error dispatching Discord event: ' . $e->getMessage());
            return 1;
        }

        // Check if the listener is registered
        $listeners = EventFacade::getListeners(SendEventToDiscord::class);
        if (!empty($listeners)) {
            $this->info('✓ Discord event listener is registered');
            $this->info('Registered listeners: ' . count($listeners));
        } else {
            $this->error('✗ No Discord event listeners registered');
        }

        // Check environment variables
        $apiKey = env('DISCORD_DISCO_API_KEY');
        $hostUri = env('DISCORD_DISCO_HOST_URI');

        if ($apiKey && $hostUri) {
            $this->info('✓ Discord environment variables are configured');
            $this->info('API Key: ' . substr($apiKey, 0, 10) . '...');
            $this->info('Host URI: ' . $hostUri);
        } else {
            $this->error('✗ Discord environment variables not configured');
        }

        $this->info('Discord Integration Test Complete!');
        return 0;
    }
}
