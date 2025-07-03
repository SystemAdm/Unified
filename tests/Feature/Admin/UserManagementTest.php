<?php

namespace Tests\Feature\Admin;

use App\Enum\Permission;
use App\Enum\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can create a user', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Act as the admin
    $this->actingAs($admin);

    // Get the member role ID for creating
    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $memberRoleId = $memberRole->id;

    // Create a new user
    $response = $this->post(route('admin.users.store'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'phone' => '1234567890',
        'password' => 'password123',
        'roles' => [$memberRoleId],
    ]);

    // Assert redirect to users index
    $response->assertRedirect(route('admin.users.index'));

    // Assert the user was created
    $this->assertDatabaseHas('users', [
        'given_name' => 'New',
        'family_name' => 'User',
    ]);

    // Get the created user
    $user = User::where('given_name', 'New')->where('family_name', 'User')->first();

    // Assert email was set correctly
    expect($user->email)->toBe('newuser@example.com');

    // Assert phone was set correctly
    expect($user->phone)->toBe('1234567890');

    // Assert role was assigned
    expect($user->hasRole($memberRole))->toBeTrue();
});

test('admin cannot create a user without roles', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Act as the admin
    $this->actingAs($admin);

    // Try to create a new user without roles
    $response = $this->post(route('admin.users.store'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'phone' => '1234567890',
        'password' => 'password123',
        'roles' => [],
    ]);

    // Assert validation error
    $response->assertSessionHasErrors('roles');

    // Assert the user was not created
    $this->assertDatabaseMissing('users', [
        'given_name' => 'New',
        'family_name' => 'User',
    ]);
});

test('admin can update a user', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user to be updated
    $user = User::factory()->create();
    $user->name = 'Original Name';
    $user->email = 'original@example.com'; // Set email to create the relationship
    $user->phone = '1234567890'; // Set phone to create the relationship
    $user->save();

    // Refresh the user to ensure we have the latest data
    $user->refresh();

    // Verify the email was set correctly
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // If the email is not set correctly, set it again
    if (!$primaryEmail || $primaryEmail->address !== 'original@example.com') {
        // Delete all existing emails
        \DB::table('email_user')->where('user_id', $user->id)->delete();

        // Create a new email
        $emailModel = \App\Models\Email::firstOrCreate(['address' => 'original@example.com']);

        // Attach the email as primary
        $user->emails()->attach($emailModel, ['is_primary' => true]);

        // Save the user
        $user->save();

        // Refresh the user
        $user->refresh();
    }

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Get the initial roles
    $initialRoles = $user->roles->pluck('id')->toArray();

    // Act as the admin
    $this->actingAs($admin);

    // Get the admin role ID for updating
    $adminRoleId = $adminRole->id;

    // Update the user
    $response = $this->put(route('admin.users.update', ['user' => $user->id]), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '0987654321', // Update phone number
        'password' => 'newpassword123',
        'roles' => [$adminRoleId], // Change role to admin
    ]);

    // Assert redirect to users index
    $response->assertRedirect(route('admin.users.index'));

    // Refresh the user from the database
    $user->refresh();

    // Get the primary email directly from the database
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // Get the primary phone directly from the database
    $primaryPhone = \DB::table('phone_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('phones', 'phones.id', '=', 'phone_user.phone_id')
        ->select('phones.number')
        ->first();

    // Debug output
    dump([
        'user_id' => $user->id,
        'name' => $user->name,
        'email_from_accessor' => $user->email,
        'primary_email_from_db' => $primaryEmail ? $primaryEmail->address : null,
        'phone_from_accessor' => $user->phone,
        'primary_phone_from_db' => $primaryPhone ? $primaryPhone->number : null,
        'all_emails' => $user->emails()->get()->toArray(),
        'all_phones' => $user->phones()->get()->toArray(),
    ]);

    // Assert the user was updated
    expect($user->name)->toBe('Updated Name');

    // Assert email was NOT updated (primary email cannot be changed)
    expect($primaryEmail->address)->toBe('original@example.com');

    // Assert phone was NOT updated (primary phone cannot be changed)
    expect($user->phone)->toBe('1234567890');

    // Verify password was changed by attempting to authenticate
    expect(password_verify('newpassword123', $user->password))->toBeTrue();

    // Verify roles were updated
    $updatedRoles = $user->roles->pluck('id')->toArray();
    expect($updatedRoles)->toContain($adminRoleId);
    expect($updatedRoles)->not->toContain($memberRole->id);
});

test('admin can update a user without changing password', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user to be updated
    $user = User::factory()->create();
    $user->name = 'Original Name';
    $user->email = 'original@example.com'; // Set email to create the relationship
    $user->phone = '1234567890'; // Set phone to create the relationship
    $user->save();

    // Refresh the user to ensure we have the latest data
    $user->refresh();

    // Verify the email was set correctly
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // If the email is not set correctly, set it again
    if (!$primaryEmail || $primaryEmail->address !== 'original@example.com') {
        // Delete all existing emails
        \DB::table('email_user')->where('user_id', $user->id)->delete();

        // Create a new email
        $emailModel = \App\Models\Email::firstOrCreate(['address' => 'original@example.com']);

        // Attach the email as primary
        $user->emails()->attach($emailModel, ['is_primary' => true]);

        // Save the user
        $user->save();

        // Refresh the user
        $user->refresh();
    }

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Store the original password hash
    $originalPassword = $user->password;

    // Act as the admin
    $this->actingAs($admin);

    // Update the user without changing password
    $response = $this->put(route('admin.users.update', ['user' => $user->id]), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '0987654321', // Update phone number
        'password' => '', // Empty password should not change it
        'roles' => [$memberRole->id],
    ]);

    // Assert redirect to users index
    $response->assertRedirect(route('admin.users.index'));

    // Refresh the user from the database
    $user->refresh();

    // Get the primary email directly from the database
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // Assert the user was updated
    expect($user->name)->toBe('Updated Name');

    // Assert email was NOT updated (primary email cannot be changed)
    expect($primaryEmail->address)->toBe('original@example.com');

    // Assert phone was NOT updated (primary phone cannot be changed)
    expect($user->phone)->toBe('1234567890');

    // Verify password was NOT changed
    expect($user->password)->toBe($originalPassword);
});

test('admin can update a user with multiple roles', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user to be updated
    $user = User::factory()->create();
    $user->name = 'Original Name';
    $user->email = 'original@example.com'; // Set email to create the relationship
    $user->phone = '1234567890'; // Set phone to create the relationship
    $user->save();

    // Refresh the user to ensure we have the latest data
    $user->refresh();

    // Verify the email was set correctly
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // If the email is not set correctly, set it again
    if (!$primaryEmail || $primaryEmail->address !== 'original@example.com') {
        // Delete all existing emails
        \DB::table('email_user')->where('user_id', $user->id)->delete();

        // Create a new email
        $emailModel = \App\Models\Email::firstOrCreate(['address' => 'original@example.com']);

        // Attach the email as primary
        $user->emails()->attach($emailModel, ['is_primary' => true]);

        // Save the user
        $user->save();

        // Refresh the user
        $user->refresh();
    }

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Act as the admin
    $this->actingAs($admin);

    // Get the admin and moderator role IDs for updating
    $adminRoleId = $adminRole->id;
    $moderatorRole = RoleModel::findByName(Role::MODERATOR->value);
    $moderatorRoleId = $moderatorRole->id;

    // Update the user with multiple roles
    $response = $this->put(route('admin.users.update', ['user' => $user->id]), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '0987654321', // Update phone number
        'password' => 'newpassword123',
        'roles' => [$adminRoleId, $moderatorRoleId], // Assign multiple roles
    ]);

    // Assert redirect to users index
    $response->assertRedirect(route('admin.users.index'));

    // Refresh the user from the database
    $user->refresh();

    // Get the primary email directly from the database
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // Assert the user was updated
    expect($user->name)->toBe('Updated Name');

    // Assert email was NOT updated (primary email cannot be changed)
    expect($primaryEmail->address)->toBe('original@example.com');

    // Assert phone was NOT updated (primary phone cannot be changed)
    expect($user->phone)->toBe('1234567890');

    // Verify password was changed by attempting to authenticate
    expect(password_verify('newpassword123', $user->password))->toBeTrue();

    // Verify roles were updated to include both roles
    $updatedRoles = $user->roles->pluck('id')->toArray();
    expect($updatedRoles)->toContain($adminRoleId);
    expect($updatedRoles)->toContain($moderatorRoleId);
    expect($updatedRoles)->not->toContain($memberRole->id);
    expect(count($updatedRoles))->toBe(2); // Ensure exactly 2 roles are assigned
});

test('admin cannot update a user without roles', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user to be updated
    $user = User::factory()->create();
    $user->name = 'Original Name';
    $user->email = 'original@example.com'; // Set email to create the relationship
    $user->phone = '1234567890'; // Set phone to create the relationship
    $user->save();

    // Refresh the user to ensure we have the latest data
    $user->refresh();

    // Verify the email was set correctly
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // If the email is not set correctly, set it again
    if (!$primaryEmail || $primaryEmail->address !== 'original@example.com') {
        // Delete all existing emails
        \DB::table('email_user')->where('user_id', $user->id)->delete();

        // Create a new email
        $emailModel = \App\Models\Email::firstOrCreate(['address' => 'original@example.com']);

        // Attach the email as primary
        $user->emails()->attach($emailModel, ['is_primary' => true]);

        // Save the user
        $user->save();

        // Refresh the user
        $user->refresh();
    }

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $user->assignRole($memberRole);

    // Act as the admin
    $this->actingAs($admin);

    // Try to update the user without roles
    $response = $this->put(route('admin.users.update', ['user' => $user->id]), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '0987654321',
        'password' => 'newpassword123',
        'roles' => [], // Empty roles array
    ]);

    // Assert validation error
    $response->assertSessionHasErrors('roles');

    // Refresh the user from the database
    $user->refresh();

    // Get the primary email directly from the database
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $user->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // Assert the user was NOT updated
    expect($user->name)->toBe('Original Name');
    expect($primaryEmail->address)->toBe('original@example.com');
    expect($user->phone)->toBe('1234567890');

    // Verify roles were not changed
    expect($user->hasRole($memberRole))->toBeTrue();
});

test('non-admin users cannot update users', function () {
    // Create a regular user with member role
    $regularUser = User::factory()->create();
    $regularUser->email = 'regular@example.com'; // Set email to create the relationship
    $regularUser->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $regularUser->assignRole($memberRole);

    // Create another user to be updated
    $userToUpdate = User::factory()->create();
    $userToUpdate->name = 'Original Name';
    $userToUpdate->email = 'original@example.com'; // Set email to create the relationship
    $userToUpdate->phone = '1234567890'; // Set phone to create the relationship
    $userToUpdate->save();

    // Refresh the user to ensure we have the latest data
    $userToUpdate->refresh();

    // Verify the email was set correctly
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $userToUpdate->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // If the email is not set correctly, set it again
    if (!$primaryEmail || $primaryEmail->address !== 'original@example.com') {
        // Delete all existing emails
        \DB::table('email_user')->where('user_id', $userToUpdate->id)->delete();

        // Create a new email
        $emailModel = \App\Models\Email::firstOrCreate(['address' => 'original@example.com']);

        // Attach the email as primary
        $userToUpdate->emails()->attach($emailModel, ['is_primary' => true]);

        // Save the user
        $userToUpdate->save();

        // Refresh the user
        $userToUpdate->refresh();
    }

    // Act as the regular user
    $this->actingAs($regularUser);

    // Try to update the user
    $response = $this->put(route('admin.users.update', ['user' => $userToUpdate->id]), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '0987654321',
        'password' => 'newpassword123',
        'roles' => [$memberRole->id],
    ]);

    // Assert forbidden status
    $response->assertStatus(403);

    // Refresh the user from the database
    $userToUpdate->refresh();

    // Get the primary email directly from the database
    $primaryEmail = \DB::table('email_user')
        ->where('user_id', $userToUpdate->id)
        ->where('is_primary', true)
        ->join('emails', 'emails.id', '=', 'email_user.email_id')
        ->select('emails.address')
        ->first();

    // Assert the user was NOT updated
    expect($userToUpdate->name)->toBe('Original Name');
    expect($primaryEmail->address)->toBe('original@example.com');
    expect($userToUpdate->phone)->toBe('1234567890');
});
