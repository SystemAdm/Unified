<?php

namespace Tests\Feature;

use App\Enum\Role;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
});

test('user can view events index', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create some published events
    $publishedEvents = Event::factory()->count(3)->create(['status' => 'published']);

    // Create some draft events (should not be visible to users)
    $draftEvents = Event::factory()->count(2)->create(['status' => 'draft']);

    // Act as the user
    $this->actingAs($user);

    // Visit the events index page
    $response = $this->get(route('events.index'));

    // Assert successful response
    $response->assertStatus(200);

    // Assert the published events are displayed
    foreach ($publishedEvents as $event) {
        $response->assertSee($event->title);
    }

    // Assert the draft events are not displayed
    foreach ($draftEvents as $event) {
        $response->assertDontSee($event->title);
    }
});

test('user can view event details', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create a published event
    $event = Event::factory()->create([
        'status' => 'published',
        'title' => 'Test Event',
        'description' => 'This is a test event description',
    ]);

    // Act as the user
    $this->actingAs($user);

    // Visit the event details page
    $response = $this->get(route('events.show', ['event' => $event->id]));

    // Assert successful response
    $response->assertStatus(200);

    // Assert the event details are displayed
    $response->assertSee($event->title);
    $response->assertSee($event->description);
});

test('user cannot view unpublished event details', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create a draft event
    $event = Event::factory()->create([
        'status' => 'draft',
        'title' => 'Draft Event',
    ]);

    // Act as the user
    $this->actingAs($user);

    // Try to visit the event details page
    $response = $this->get(route('events.show', ['event' => $event->id]));

    // Assert not found response
    $response->assertStatus(404);
});

test('user can signup for an event', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create an event with signup enabled
    $event = Event::factory()->create([
        'status' => 'published',
        'has_signup' => true,
        'signup_start_date' => now()->subDays(1),
        'signup_end_date' => now()->addDays(5),
    ]);

    // Act as the user
    $this->actingAs($user);

    // Signup for the event
    $response = $this->post(route('events.signup', ['event' => $event->id]));

    // Assert redirect to event details
    $response->assertRedirect(route('events.show', ['event' => $event->id]));

    // Assert the user is now in the signupped list
    $this->assertTrue($event->signupped->contains($user->id));
});

test('user cannot signup for an event with closed signup', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create an event with signup disabled
    $event = Event::factory()->create([
        'status' => 'published',
        'has_signup' => false,
    ]);

    // Act as the user
    $this->actingAs($user);

    // Try to signup for the event
    $response = $this->post(route('events.signup', ['event' => $event->id]));

    // Assert forbidden status
    $response->assertStatus(403);

    // Assert the user is not in the signupped list
    $this->assertFalse($event->signupped->contains($user->id));
});

test('user can remove signup from an event', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create an event
    $event = Event::factory()->create([
        'status' => 'published',
    ]);

    // Add the user to the signupped list
    $event->signupped()->attach($user->id);

    // Act as the user
    $this->actingAs($user);

    // Remove signup from the event
    $response = $this->delete(route('events.remove-signup', ['event' => $event->id]));

    // Assert redirect to event details
    $response->assertRedirect(route('events.show', ['event' => $event->id]));

    // Assert the user is no longer in the signupped list
    $this->assertFalse($event->signupped->contains($user->id));
});

test('user can join an event', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create an event that has started
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->subHours(1),
        'end_date' => now()->addHours(5),
    ]);

    // Act as the user
    $this->actingAs($user);

    // Join the event
    $response = $this->post(route('events.join', ['event' => $event->id]));

    // Assert redirect to event details
    $response->assertRedirect(route('events.show', ['event' => $event->id]));

    // Assert the user is now in the registered list
    $this->assertTrue($event->registered->contains($user->id));
});

test('user cannot join an event that has not started', function () {
    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Create an event that has not started
    $event = Event::factory()->create([
        'status' => 'published',
        'start_date' => now()->addHours(5),
        'end_date' => now()->addHours(10),
    ]);

    // Act as the user
    $this->actingAs($user);

    // Try to join the event
    $response = $this->post(route('events.join', ['event' => $event->id]));

    // Assert forbidden status
    $response->assertStatus(403);

    // Assert the user is not in the registered list
    $this->assertFalse($event->registered->contains($user->id));
});
