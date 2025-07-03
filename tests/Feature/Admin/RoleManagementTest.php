<?php

namespace Tests\Feature\Admin;

use App\Enum\Permission;
use App\Enum\Role as RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
});

test('admin can view roles list', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(RoleEnum::ADMIN->value);
    $admin->assignRole($adminRole);

    // Act as the admin
    $this->actingAs($admin);

    // Visit the roles index page
    $response = $this->get(route('admin.roles.index'));

    // Assert successful response
    $response->assertStatus(200);

    // Assert that the page contains the built-in roles
    $response->assertInertia(fn ($page) => $page
        ->component('admin/roles/Index')
        ->has('roles')
    );
});

test('admin can create a new role', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(RoleEnum::ADMIN->value);
    $admin->assignRole($adminRole);

    // Get some permissions to assign to the new role
    $permissions = PermissionModel::take(3)->get()->pluck('id')->toArray();

    // Act as the admin
    $this->actingAs($admin);

    // Create a new role
    $response = $this->post(route('admin.roles.store'), [
        'name' => 'Test Role',
        'permissions' => $permissions,
    ]);

    // Assert redirect to roles index
    $response->assertRedirect(route('admin.roles.index'));

    // Assert the role was created
    $this->assertDatabaseHas('roles', [
        'name' => 'Test Role',
    ]);

    // Get the created role
    $role = RoleModel::where('name', 'Test Role')->first();

    // Assert permissions were assigned
    foreach ($permissions as $permissionId) {
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permissionId,
        ]);
    }
});

test('admin can update a role', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(RoleEnum::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a role to update
    $role = RoleModel::create(['name' => 'Role to Update']);

    // Get initial permissions
    $initialPermissions = PermissionModel::take(2)->get();
    $role->syncPermissions($initialPermissions);

    // Get different permissions for update
    $newPermissions = PermissionModel::skip(2)->take(2)->get()->pluck('id')->toArray();

    // Act as the admin
    $this->actingAs($admin);

    // Update the role
    $response = $this->put(route('admin.roles.update', ['role' => $role->id]), [
        'name' => 'Updated Role Name',
        'permissions' => $newPermissions,
    ]);

    // Assert redirect to roles index
    $response->assertRedirect(route('admin.roles.index'));

    // Refresh the role from the database
    $role->refresh();

    // Assert the role was updated
    expect($role->name)->toBe('Updated Role Name');

    // Assert permissions were updated
    foreach ($newPermissions as $permissionId) {
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permissionId,
        ]);
    }

    // Assert old permissions were removed
    foreach ($initialPermissions as $permission) {
        $this->assertDatabaseMissing('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }
});

test('admin can delete a custom role', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(RoleEnum::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a custom role to delete
    $role = RoleModel::create(['name' => 'Custom Role to Delete']);

    // Act as the admin
    $this->actingAs($admin);

    // Delete the role
    $response = $this->delete(route('admin.roles.destroy', ['role' => $role->id]));

    // Assert redirect to roles index
    $response->assertRedirect(route('admin.roles.index'));

    // Assert the role was deleted
    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

test('admin cannot delete built-in roles', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com';
    $admin->save();

    $adminRole = RoleModel::findByName(RoleEnum::ADMIN->value);
    $admin->assignRole($adminRole);

    // Act as the admin
    $this->actingAs($admin);

    // Try to delete a built-in role (Admin)
    $response = $this->delete(route('admin.roles.destroy', ['role' => $adminRole->id]));

    // Assert redirect to roles index with error
    $response->assertRedirect(route('admin.roles.index'));
    $response->assertSessionHas('error', 'Cannot delete built-in roles');

    // Assert the role was not deleted
    $this->assertDatabaseHas('roles', [
        'id' => $adminRole->id,
    ]);
});

test('non-admin users cannot manage roles', function () {
    // Create a regular user with member role
    $regularUser = User::factory()->create();
    $regularUser->email = 'regular@example.com';
    $regularUser->save();

    $memberRole = RoleModel::findByName(RoleEnum::MEMBER->value);
    $regularUser->assignRole($memberRole);

    // Act as the regular user
    $this->actingAs($regularUser);

    // Try to view roles
    $response = $this->get(route('admin.roles.index'));
    $response->assertStatus(403);

    // Try to create a role
    $response = $this->post(route('admin.roles.store'), [
        'name' => 'Test Role',
        'permissions' => [1, 2, 3],
    ]);
    $response->assertStatus(403);

    // Get an existing role
    $role = RoleModel::first();

    // Try to update a role
    $response = $this->put(route('admin.roles.update', ['role' => $role->id]), [
        'name' => 'Updated Role',
        'permissions' => [1, 2, 3],
    ]);
    $response->assertStatus(403);

    // Try to delete a role
    $response = $this->delete(route('admin.roles.destroy', ['role' => $role->id]));
    $response->assertStatus(403);
});
