<?php

namespace Tests\Feature\Admin;

use App\Enum\Permission;
use App\Enum\Role;
use App\Models\Event;
use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
});

test('admin can view events index', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create some events
    $events = Event::factory()->count(3)->create();

    // Act as the admin
    $this->actingAs($admin);

    // Visit the events index page
    $response = $this->get(route('admin.events.index'));

    // Assert successful response
    $response->assertStatus(200);

    // Assert the events are displayed
    foreach ($events as $event) {
        $response->assertSee($event->title);
    }
});

test('admin can create an event with all fields', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a location
    $location = Location::factory()->create();

    // Create some users and organizations for organizers
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $organization1 = \App\Models\Organization::factory()->create();
    $organization2 = \App\Models\Organization::factory()->create();

    // Act as the admin
    $this->actingAs($admin);

    // Create a new event with all fields
    $response = $this->post(route('admin.events.store'), [
        'title' => 'Test Event',
        'description' => 'This is a test event',
        'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'end_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'location_id' => $location->id,
        'status' => 'published',
        'has_signup' => true,
        'signup_start_date' => now()->format('Y-m-d H:i:s'),
        'signup_end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'seats' => 100,
        'min_age' => 18,
        'max_age' => 65,
        'class_restriction' => 'Advanced',
        'restriction' => 'members',
        'user_ids' => [$user1->id, $user2->id],
        'organization_ids' => [$organization1->id, $organization2->id],
    ]);

    // Assert redirect to events index
    $response->assertRedirect(route('admin.events.index'));

    // Assert the event was created with all fields
    $this->assertDatabaseHas('events', [
        'title' => 'Test Event',
        'description' => 'This is a test event',
        'status' => 'published',
        'has_signup' => 1, // true is stored as 1 in the database
        'seats' => 100,
        'min_age' => 18,
        'max_age' => 65,
        'class_restriction' => 'Advanced',
        'restriction' => 'members',
    ]);

    // Get the created event
    $event = Event::where('title', 'Test Event')->first();

    // Assert the event has the correct organizers
    $this->assertTrue($event->organizers->contains($user1->id));
    $this->assertTrue($event->organizers->contains($user2->id));
    $this->assertTrue($event->organizations->contains($organization1->id));
    $this->assertTrue($event->organizations->contains($organization2->id));
});

test('admin can update an event with all fields', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a location
    $location = Location::factory()->create();
    $newLocation = Location::factory()->create();

    // Create some users and organizations for organizers
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $organization1 = \App\Models\Organization::factory()->create();
    $organization2 = \App\Models\Organization::factory()->create();

    // Create an event to update
    $event = Event::factory()->create([
        'title' => 'Original Title',
        'description' => 'Original Description',
        'location_id' => $location->id,
        'has_signup' => false,
        'seats' => -1,
        'restriction' => 'everyone',
    ]);

    // Add initial organizers
    $initialUser = User::factory()->create();
    $initialOrg = \App\Models\Organization::factory()->create();
    $event->organizers()->attach($initialUser->id);
    $event->organizations()->attach($initialOrg->id);

    // Act as the admin
    $this->actingAs($admin);

    // Update the event with all fields
    $response = $this->put(route('admin.events.update', ['event' => $event->id]), [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
        'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'end_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'location_id' => $newLocation->id,
        'status' => 'published',
        'has_signup' => true,
        'signup_start_date' => now()->format('Y-m-d H:i:s'),
        'signup_end_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'seats' => 50,
        'min_age' => 21,
        'max_age' => 60,
        'class_restriction' => 'Intermediate',
        'restriction' => 'crew',
        'user_ids' => [$user1->id, $user2->id],
        'organization_ids' => [$organization1->id, $organization2->id],
    ]);

    // Assert redirect to events index
    $response->assertRedirect(route('admin.events.index'));

    // Assert the event was updated with all fields
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'Updated Title',
        'description' => 'Updated Description',
        'location_id' => $newLocation->id,
        'has_signup' => 1, // true is stored as 1 in the database
        'seats' => 50,
        'min_age' => 21,
        'max_age' => 60,
        'class_restriction' => 'Intermediate',
        'restriction' => 'crew',
    ]);

    // Refresh the event from the database
    $event->refresh();

    // Assert the event has the correct organizers (old ones should be replaced)
    $this->assertFalse($event->organizers->contains($initialUser->id));
    $this->assertFalse($event->organizations->contains($initialOrg->id));
    $this->assertTrue($event->organizers->contains($user1->id));
    $this->assertTrue($event->organizers->contains($user2->id));
    $this->assertTrue($event->organizations->contains($organization1->id));
    $this->assertTrue($event->organizations->contains($organization2->id));
});

test('admin can delete an event', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event to delete
    $event = Event::factory()->create();

    // Act as the admin
    $this->actingAs($admin);

    // Delete the event
    $response = $this->delete(route('admin.events.destroy', ['event' => $event->id]));

    // Assert redirect to events index
    $response->assertRedirect(route('admin.events.index'));

    // Assert the event was deleted
    $this->assertDatabaseMissing('events', [
        'id' => $event->id,
    ]);
});

test('admin can force start signup for an event', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event with signup disabled
    $event = Event::factory()->create([
        'has_signup' => false,
        'signup_start_date' => now()->addDays(5),
        'signup_end_date' => now()->addDays(10),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Force start signup
    $response = $this->patch(route('admin.events.force-start-signup', ['event' => $event->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Refresh the event from the database
    $event->refresh();

    // Assert signup is now enabled and start date is now
    $this->assertTrue($event->has_signup);
    $this->assertTrue($event->signup_start_date->isPast() || $event->signup_start_date->isCurrentDay());
});

test('admin can force end signup for an event', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event with signup enabled
    $event = Event::factory()->create([
        'has_signup' => true,
        'signup_start_date' => now()->subDays(5),
        'signup_end_date' => now()->addDays(5),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Force end signup
    $response = $this->patch(route('admin.events.force-end-signup', ['event' => $event->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Refresh the event from the database
    $event->refresh();

    // Assert signup end date is now in the past
    $this->assertTrue($event->signup_end_date->isPast() || $event->signup_end_date->isCurrentDay());
});

test('admin can force start an event', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event with future start date
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Force start event
    $response = $this->patch(route('admin.events.force-start-event', ['event' => $event->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Refresh the event from the database
    $event->refresh();

    // Assert event start date is now
    $this->assertTrue($event->start_date->isPast() || $event->start_date->isCurrentDay());
});

test('admin can force end an event', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event with past start date and future end date
    $event = Event::factory()->create([
        'start_date' => now()->subDays(5),
        'end_date' => now()->addDays(5),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Force end event
    $response = $this->patch(route('admin.events.force-end-event', ['event' => $event->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Refresh the event from the database
    $event->refresh();

    // Assert event end date is now
    $this->assertTrue($event->end_date->isPast() || $event->end_date->isCurrentDay());
});

test('admin can cancel an event with reason', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event
    $event = Event::factory()->create([
        'is_cancelled' => false,
        'cancelled_at' => null,
        'cancellation_reason' => null,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Cancel the event with a reason
    $response = $this->patch(route('admin.events.cancel', ['event' => $event->id]), [
        'cancellation_reason' => 'Test cancellation reason',
    ]);

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Refresh the event from the database
    $event->refresh();

    // Assert event is cancelled with the provided reason
    $this->assertTrue($event->is_cancelled);
    $this->assertNotNull($event->cancelled_at);
    $this->assertEquals('Test cancellation reason', $event->cancellation_reason);
});

test('admin can copy user from signup to registered', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event
    $event = Event::factory()->create();

    // Create a user who has signed up for the event
    $user = User::factory()->create();
    $event->signupped()->attach($user->id);

    // Act as the admin
    $this->actingAs($admin);

    // Copy user from signup to registered
    $response = $this->post(route('admin.events.copy-to-registered', ['event' => $event->id, 'user' => $user->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Assert user is now in both signup and registered lists
    $this->assertTrue($event->signupped->contains($user->id));
    $this->assertTrue($event->registered->contains($user->id));
});

test('admin can remove user from signup, registered and attending', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event
    $event = Event::factory()->create();

    // Create a user who has signed up, registered, and is attending the event
    $user = User::factory()->create();
    $event->signupped()->attach($user->id);
    $event->registered()->attach($user->id);
    $event->attending()->attach($user->id);

    // Act as the admin
    $this->actingAs($admin);

    // Remove user from all lists
    $response = $this->delete(route('admin.events.remove-from-all', ['event' => $event->id, 'user' => $user->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Assert user is removed from all lists
    $this->assertFalse($event->signupped->contains($user->id));
    $this->assertFalse($event->registered->contains($user->id));
    $this->assertFalse($event->attending->contains($user->id));
});

test('admin can copy user from registered to attending', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event
    $event = Event::factory()->create();

    // Create a user who has registered for the event
    $user = User::factory()->create();
    $event->registered()->attach($user->id);

    // Act as the admin
    $this->actingAs($admin);

    // Copy user from registered to attending
    $response = $this->post(route('admin.events.copy-to-attending', ['event' => $event->id, 'user' => $user->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Assert user is now in both registered and attending lists
    $this->assertTrue($event->registered->contains($user->id));
    $this->assertTrue($event->attending->contains($user->id));
});

test('admin can remove user from registered and attending', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event
    $event = Event::factory()->create();

    // Create a user who has registered and is attending the event
    $user = User::factory()->create();
    $event->registered()->attach($user->id);
    $event->attending()->attach($user->id);

    // Act as the admin
    $this->actingAs($admin);

    // Remove user from registered and attending
    $response = $this->delete(route('admin.events.remove-from-registered-attending', ['event' => $event->id, 'user' => $user->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Assert user is removed from registered and attending lists
    $this->assertFalse($event->registered->contains($user->id));
    $this->assertFalse($event->attending->contains($user->id));
});

test('admin can remove user from attending', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an event
    $event = Event::factory()->create();

    // Create a user who is attending the event
    $user = User::factory()->create();
    $event->attending()->attach($user->id);

    // Act as the admin
    $this->actingAs($admin);

    // Remove user from attending
    $response = $this->delete(route('admin.events.remove-from-attending', ['event' => $event->id, 'user' => $user->id]));

    // Assert redirect to events show page
    $response->assertRedirect(route('admin.events.show', ['event' => $event->id]));

    // Assert user is removed from attending list
    $this->assertFalse($event->attending->contains($user->id));
});

test('non-admin cannot access event management', function () {
    // Create a regular user
    $user = User::factory()->create();
    $user->email = 'user@example.com';
    $user->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Act as the regular user
    $this->actingAs($user);

    // Try to access events index
    $response = $this->get(route('admin.events.index'));

    // Assert forbidden status
    $response->assertStatus(403);

    // Try to create an event
    $createResponse = $this->post(route('admin.events.store'), [
        'title' => 'Test Event',
        'description' => 'This is a test event',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'status' => 'published',
    ]);

    // Assert forbidden status
    $createResponse->assertStatus(403);
});
