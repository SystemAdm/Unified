<?php

namespace Tests\Feature\Admin;

use App\Enum\Permission;
use App\Enum\Role;
use App\Models\Event;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view locations index', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create some locations
    Location::create([
        'name' => 'Test Location 1',
        'description' => 'Test Description 1',
        'address' => '123 Test St',
        'city' => 'Test City',
        'state' => 'Test State',
        'country' => 'Test Country',
        'postal_code' => '12345',
        'is_active' => true,
    ]);

    Location::create([
        'name' => 'Test Location 2',
        'description' => 'Test Description 2',
        'address' => '456 Test Ave',
        'city' => 'Test City 2',
        'state' => 'Test State 2',
        'country' => 'Test Country 2',
        'postal_code' => '67890',
        'is_active' => true,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Visit the locations index page
    $response = $this->get(route('admin.locations.index'));

    // Assert successful response
    $response->assertStatus(200);

    // Assert the page contains the locations
    $response->assertInertia(fn ($page) => $page
        ->component('admin/locations/Index')
        ->has('locations.data', 2)
        ->where('locations.data.0.name', 'Test Location 1')
        ->where('locations.data.1.name', 'Test Location 2')
    );
});

test('admin can create a location', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Act as the admin
    $this->actingAs($admin);

    // Create a new location
    $response = $this->post(route('admin.locations.store'), [
        'name' => 'New Test Location',
        'description' => 'New Test Description',
        'address' => '789 New St',
        'city' => 'New City',
        'state' => 'New State',
        'country' => 'New Country',
        'postal_code' => '54321',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'capacity' => '100',
        'is_active' => true,
    ]);

    // Assert redirect to locations index
    $response->assertRedirect(route('admin.locations.index'));

    // Assert the location was created
    $this->assertDatabaseHas('locations', [
        'name' => 'New Test Location',
        'description' => 'New Test Description',
        'address' => '789 New St',
        'city' => 'New City',
        'state' => 'New State',
        'country' => 'New Country',
        'postal_code' => '54321',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'capacity' => '100',
        'is_active' => 1,
    ]);
});

test('admin can update a location', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a location to update
    $location = Location::create([
        'name' => 'Location to Update',
        'description' => 'Description to Update',
        'address' => '123 Update St',
        'city' => 'Update City',
        'state' => 'Update State',
        'country' => 'Update Country',
        'postal_code' => '12345',
        'is_active' => true,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Update the location
    $response = $this->put(route('admin.locations.update', ['location' => $location->id]), [
        'name' => 'Updated Location',
        'description' => 'Updated Description',
        'address' => '456 Updated St',
        'city' => 'Updated City',
        'state' => 'Updated State',
        'country' => 'Updated Country',
        'postal_code' => '54321',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'capacity' => '200',
        'is_active' => false,
    ]);

    // Assert redirect to locations index
    $response->assertRedirect(route('admin.locations.index'));

    // Assert the location was updated
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'name' => 'Updated Location',
        'description' => 'Updated Description',
        'address' => '456 Updated St',
        'city' => 'Updated City',
        'state' => 'Updated State',
        'country' => 'Updated Country',
        'postal_code' => '54321',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'capacity' => '200',
        'is_active' => 0,
    ]);
});

test('admin can delete a location', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a location to delete
    $location = Location::create([
        'name' => 'Location to Delete',
        'description' => 'Description to Delete',
        'address' => '123 Delete St',
        'city' => 'Delete City',
        'state' => 'Delete State',
        'country' => 'Delete Country',
        'postal_code' => '12345',
        'is_active' => true,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Delete the location
    $response = $this->delete(route('admin.locations.destroy', ['location' => $location->id]));

    // Assert redirect to locations index
    $response->assertRedirect(route('admin.locations.index'));

    // Assert the location was deleted
    $this->assertDatabaseMissing('locations', [
        'id' => $location->id,
    ]);
});

test('admin cannot delete a location used by events', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a location
    $location = Location::create([
        'name' => 'Location with Events',
        'description' => 'Description',
        'address' => '123 Event St',
        'city' => 'Event City',
        'state' => 'Event State',
        'country' => 'Event Country',
        'postal_code' => '12345',
        'is_active' => true,
    ]);

    // Create an event associated with the location
    $event = Event::create([
        'title' => 'Test Event',
        'description' => 'Test Event Description',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'location_id' => $location->id,
        'is_active' => true,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Try to delete the location
    $response = $this->delete(route('admin.locations.destroy', ['location' => $location->id]));

    // Assert redirect to locations index with error message
    $response->assertRedirect(route('admin.locations.index'));
    $response->assertSessionHas('error', 'Cannot delete location because it is being used by events.');

    // Assert the location was not deleted
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
    ]);
});

test('non-admin users cannot manage locations', function () {
    // Create a regular user with member role
    $regularUser = User::factory()->create();
    $regularUser->email = 'regular@example.com';
    $regularUser->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $regularUser->assignRole($memberRole);

    // Create a location
    $location = Location::create([
        'name' => 'Test Location',
        'description' => 'Test Description',
        'address' => '123 Test St',
        'city' => 'Test City',
        'state' => 'Test State',
        'country' => 'Test Country',
        'postal_code' => '12345',
        'is_active' => true,
    ]);

    // Act as the regular user
    $this->actingAs($regularUser);

    // Try to access location management routes
    $routes = [
        'index' => $this->get(route('admin.locations.index')),
        'create' => $this->get(route('admin.locations.create')),
        'store' => $this->post(route('admin.locations.store'), [
            'name' => 'New Location',
            'description' => 'New Description',
            'is_active' => true,
        ]),
        'show' => $this->get(route('admin.locations.show', ['location' => $location->id])),
        'edit' => $this->get(route('admin.locations.edit', ['location' => $location->id])),
        'update' => $this->put(route('admin.locations.update', ['location' => $location->id]), [
            'name' => 'Updated Location',
            'description' => 'Updated Description',
            'is_active' => false,
        ]),
        'destroy' => $this->delete(route('admin.locations.destroy', ['location' => $location->id])),
    ];

    // Assert all routes return forbidden status
    foreach ($routes as $route => $response) {
        $response->assertStatus(403);
    }
});
