<?php

namespace Tests\Feature;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscordBotApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set the API key for testing
        config(['app.env' => 'testing']);
        config(['app.discord_bot_api_key' => 'test-api-key']);
        $_ENV['DISCORD_BOT_API_KEY'] = 'test-api-key';
    }

    private function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'X-API-Key' => 'test-api-key'
        ];
    }

    public function test_create_event_successfully()
    {
        $data = [
            'event_date' => '18/07/2025',
            'custom_title' => 'Special Event',
            'restricted' => 'members'
        ];

        $response = $this->postJson('/api/create-event', $data, $this->getHeaders());

        // Debug output - always show
        dump('Response status: ' . $response->status());
        dump('Response content: ' . $response->content());
        dump('Expected API key: ' . env('DISCORD_BOT_API_KEY'));
        dump('Headers sent: ', $this->getHeaders());

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Event created successfully'
                ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Special Event',
            'restriction' => 'members'
        ]);
    }

    public function test_create_event_with_invalid_api_key()
    {
        $data = [
            'event_date' => '18/07/2025',
            'custom_title' => 'Special Event',
            'restricted' => 'members'
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'X-API-Key' => 'invalid-key'
        ];

        $response = $this->postJson('/api/create-event', $data, $headers);

        $response->assertStatus(401)
                ->assertJson([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid or missing API key'
                ]);
    }

    public function test_create_event_with_missing_data()
    {
        $data = [
            'event_date' => '18/07/2025'
            // Missing custom_title and restricted
        ];

        $response = $this->postJson('/api/create-event', $data, $this->getHeaders());

        $response->assertStatus(422)
                ->assertJson([
                    'error' => 'Validation failed'
                ]);
    }

    public function test_cancel_event_successfully()
    {
        // First create an event
        $event = Event::create([
            'title' => 'Test Event',
            'start_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025'),
            'end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->addHours(2),
            'restriction' => 'members',
            'status' => 'published',
            'has_signup' => true,
            'signup_start_date' => now(),
            'signup_end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->subHour(),
        ]);

        $data = [
            'event_date' => '18/07/2025'
        ];

        $response = $this->postJson('/api/cancel-event', $data, $this->getHeaders());

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Event cancelled successfully'
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'is_cancelled' => true
        ]);
    }

    public function test_delete_event_successfully()
    {
        // First create an event
        $event = Event::create([
            'title' => 'Test Event',
            'start_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025'),
            'end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->addHours(2),
            'restriction' => 'members',
            'status' => 'published',
            'has_signup' => true,
            'signup_start_date' => now(),
            'signup_end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->subHour(),
        ]);

        $data = [
            'event_date' => '18/07/2025'
        ];

        $response = $this->postJson('/api/delete-event', $data, $this->getHeaders());

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ]);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id
        ]);
    }

    public function test_update_event_restriction_successfully()
    {
        // First create an event
        $event = Event::create([
            'title' => 'Test Event',
            'start_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025'),
            'end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->addHours(2),
            'restriction' => 'members',
            'status' => 'published',
            'has_signup' => true,
            'signup_start_date' => now(),
            'signup_end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->subHour(),
        ]);

        $data = [
            'event_date' => '18/07/2025',
            'restriction' => 'crew'
        ];

        $response = $this->postJson('/api/update-event-restriction', $data, $this->getHeaders());

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Event restriction updated successfully'
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'restriction' => 'crew'
        ]);
    }

    public function test_update_event_successfully()
    {
        // First create an event
        $event = Event::create([
            'title' => 'Test Event',
            'start_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025'),
            'end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->addHours(2),
            'restriction' => 'members',
            'status' => 'published',
            'has_signup' => true,
            'signup_start_date' => now(),
            'signup_end_date' => Carbon::createFromFormat('d/m/Y', '18/07/2025')->subHour(),
        ]);

        $data = [
            'event_date' => '18/07/2025',
            'new_title' => 'Updated Event Title',
            'new_date' => '19/07/2025'
        ];

        $response = $this->postJson('/api/update-event', $data, $this->getHeaders());

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Event updated successfully'
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title'
        ]);
    }

    public function test_event_not_found_returns_404()
    {
        $data = [
            'event_date' => '25/12/2025'
        ];

        $response = $this->postJson('/api/cancel-event', $data, $this->getHeaders());

        $response->assertStatus(404)
                ->assertJson([
                    'error' => 'Event not found for the specified date'
                ]);
    }
}
