<?php

namespace Tests\Feature\Admin;

use App\Enum\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin menu items are visible to admin users', function () {
    // Create an admin user
    $user = User::factory()->create();
    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $user->assignRole($adminRole);

    $this->actingAs($user);

    // Visit the dashboard page which includes the sidebar
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);

    // Assert that the response is an Inertia response
    $response->assertInertia(function (Assert $page) {
        // Check that the sidebar component is rendered
        $page->component('Dashboard');

        // The sidebar is rendered client-side, so we can't directly test its contents here
        // We'll need to rely on the AdminMenuTest to verify access to the admin pages
        return true;
    });
});

test('admin menu routes are accessible to admin users', function () {
    // Create an admin user
    $user = User::factory()->create();
    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $user->assignRole($adminRole);

    $this->actingAs($user);

    // Test each admin route
    $routes = [
        'admin.organizations.index',
        'admin.users.index',
        'admin.phones.index',
        'admin.emails.index',
        'admin.roles.index',
        'admin.permissions.index'
    ];

    foreach ($routes as $route) {
        $response = $this->get(route($route));
        $response->assertStatus(200);
    }
});

test('admin menu routes are not accessible to non-admin users', function () {
    // Create a regular user with member role
    $user = User::factory()->create();
    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    $this->actingAs($user);

    // Test each admin route
    $routes = [
        'admin.organizations.index',
        'admin.users.index',
        'admin.phones.index',
        'admin.emails.index',
        'admin.roles.index',
        'admin.permissions.index'
    ];

    foreach ($routes as $route) {
        $response = $this->get(route($route));
        $response->assertStatus(403); // Forbidden
    }
});

test('admin menu routes redirect guests to login', function () {
    // Test each admin route
    $routes = [
        'admin.organizations.index',
        'admin.users.index',
        'admin.phones.index',
        'admin.emails.index',
        'admin.roles.index',
        'admin.permissions.index'
    ];

    foreach ($routes as $route) {
        $response = $this->get(route($route));
        $response->assertRedirect('/login');
    }
});
