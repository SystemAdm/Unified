<?php

namespace Tests\Feature\Admin;

use App\Enum\Permission;
use App\Enum\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin users can access the organizations page', function () {
    // Create an admin user
    $user = User::factory()->create();
    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $user->assignRole($adminRole);

    $this->actingAs($user);

    // Visit the organizations page
    $response = $this->get(route('admin.organizations.index'));
    $response->assertStatus(200);
});

test('admin users can access the users page', function () {
    // Create an admin user
    $user = User::factory()->create();
    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $user->assignRole($adminRole);

    $this->actingAs($user);

    // Visit the users page
    $response = $this->get(route('admin.users.index'));
    $response->assertStatus(200);
});

test('admin users can access the roles page', function () {
    // Create an admin user
    $user = User::factory()->create();
    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $user->assignRole($adminRole);

    $this->actingAs($user);

    // Visit the roles page
    $response = $this->get(route('admin.roles.index'));
    $response->assertStatus(200);
});

test('admin users can access the permissions page', function () {
    // Create an admin user
    $user = User::factory()->create();
    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $user->assignRole($adminRole);

    $this->actingAs($user);

    // Visit the permissions page
    $response = $this->get(route('admin.permissions.index'));
    $response->assertStatus(200);
});

test('non-admin users cannot access the organizations admin page', function () {
    // Create a regular user with member role
    $user = User::factory()->create();
    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    $this->actingAs($user);

    // Try to visit the organizations page
    $response = $this->get(route('admin.organizations.index'));
    $response->assertStatus(403); // Forbidden
});

test('non-admin users cannot access the users admin page', function () {
    // Create a regular user with member role
    $user = User::factory()->create();
    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    $this->actingAs($user);

    // Try to visit the users page
    $response = $this->get(route('admin.users.index'));
    $response->assertStatus(403); // Forbidden
});

test('non-admin users cannot access the roles admin page', function () {
    // Create a regular user with member role
    $user = User::factory()->create();
    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    $this->actingAs($user);

    // Try to visit the roles page
    $response = $this->get(route('admin.roles.index'));
    $response->assertStatus(403); // Forbidden
});

test('non-admin users cannot access the permissions admin page', function () {
    // Create a regular user with member role
    $user = User::factory()->create();
    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    $this->actingAs($user);

    // Try to visit the permissions page
    $response = $this->get(route('admin.permissions.index'));
    $response->assertStatus(403); // Forbidden
});

test('guests are redirected to login when accessing admin pages', function () {
    // Try to visit the organizations page
    $response = $this->get(route('admin.organizations.index'));
    $response->assertRedirect('/login');

    // Try to visit the users page
    $response = $this->get(route('admin.users.index'));
    $response->assertRedirect('/login');

    // Try to visit the roles page
    $response = $this->get(route('admin.roles.index'));
    $response->assertRedirect('/login');

    // Try to visit the permissions page
    $response = $this->get(route('admin.permissions.index'));
    $response->assertRedirect('/login');
});
