<?php

namespace Tests\Feature\Admin;

use App\Enum\Role;
use App\Models\Email;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can create an email', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user to associate with the email
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Act as the admin
    $this->actingAs($admin);

    // Create a new email
    $response = $this->post(route('admin.emails.store'), [
        'address' => 'newemail@example.com',
        'user_id' => $user->id,
        'is_primary' => true,
        'is_verified' => true,
    ]);

    // Assert redirect to emails index
    $response->assertRedirect(route('admin.emails.index'));

    // Assert the email was created
    $this->assertDatabaseHas('emails', [
        'address' => 'newemail@example.com',
    ]);

    // Get the created email
    $email = Email::where('address', 'newemail@example.com')->first();

    // Assert the email was associated with the user
    $this->assertDatabaseHas('email_user', [
        'email_id' => $email->id,
        'user_id' => $user->id,
        'is_primary' => 1,
    ]);

    // Assert the email has a verified_at timestamp
    $emailUser = \DB::table('email_user')
        ->where('email_id', $email->id)
        ->where('user_id', $user->id)
        ->first();

    expect($emailUser->verified_at)->not->toBeNull();
});

test('admin can update an email', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an email to be updated
    $email = new Email();
    $email->address = 'original@example.com';
    $email->save();

    // Act as the admin
    $this->actingAs($admin);

    // Update the email
    $response = $this->put(route('admin.emails.update', ['email' => $email->id]), [
        'address' => 'updated@example.com',
    ]);

    // Assert redirect to emails index
    $response->assertRedirect(route('admin.emails.index'));

    // Assert the original email no longer exists
    $this->assertDatabaseMissing('emails', [
        'id' => $email->id,
        'address' => 'original@example.com',
    ]);

    // Assert the new email was created
    $this->assertDatabaseHas('emails', [
        'address' => 'updated@example.com',
    ]);
});

test('admin can attach a user to an email', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an email
    $email = new Email();
    $email->address = 'test@example.com';
    $email->save();

    // Create a user to attach
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Act as the admin
    $this->actingAs($admin);

    // Attach the user to the email
    $response = $this->post(route('admin.emails.attach-user', ['email' => $email->id]), [
        'user_id' => $user->id,
        'is_primary' => true,
        'is_verified' => true,
    ]);

    // Assert redirect to email edit page
    $response->assertRedirect(route('admin.emails.edit', ['email' => $email->id]));

    // Assert the user was attached to the email
    $this->assertDatabaseHas('email_user', [
        'email_id' => $email->id,
        'user_id' => $user->id,
        'is_primary' => 1,
    ]);

    // Assert the email has a verified_at timestamp
    $emailUser = \DB::table('email_user')
        ->where('email_id', $email->id)
        ->where('user_id', $user->id)
        ->first();

    expect($emailUser->verified_at)->not->toBeNull();
});

test('admin can detach a user from an email', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an email
    $email = new Email();
    $email->address = 'test@example.com';
    $email->save();

    // Create a user to attach and then detach
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Attach the user to the email
    $user->emails()->attach($email, [
        'is_primary' => true,
        'verified_at' => now(),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Detach the user from the email
    $response = $this->delete(route('admin.emails.detach-user', ['email' => $email->id]), [
        'user_id' => $user->id,
    ]);

    // Assert redirect to email edit page
    $response->assertRedirect(route('admin.emails.edit', ['email' => $email->id]));

    // Assert the user was detached from the email
    $this->assertDatabaseMissing('email_user', [
        'email_id' => $email->id,
        'user_id' => $user->id,
    ]);
});

test('admin can set an email as primary for a user', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Create two emails
    $email1 = new Email();
    $email1->address = 'first@example.com';
    $email1->save();

    $email2 = new Email();
    $email2->address = 'second@example.com';
    $email2->save();

    // Attach both emails to the user, with email1 as primary
    $user->emails()->attach($email1, [
        'is_primary' => true,
        'verified_at' => now(),
    ]);

    $user->emails()->attach($email2, [
        'is_primary' => false,
        'verified_at' => now(),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Set email2 as primary
    $response = $this->patch(route('admin.emails.set-primary', ['email' => $email2->id]), [
        'user_id' => $user->id,
    ]);

    // Assert redirect to email edit page
    $response->assertRedirect(route('admin.emails.edit', ['email' => $email2->id]));

    // Assert email2 is now primary
    $this->assertDatabaseHas('email_user', [
        'email_id' => $email2->id,
        'user_id' => $user->id,
        'is_primary' => 1,
    ]);

    // Assert email1 is no longer primary
    $this->assertDatabaseHas('email_user', [
        'email_id' => $email1->id,
        'user_id' => $user->id,
        'is_primary' => 0,
    ]);
});

test('admin can set an email as verified for a user', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Create an email
    $email = new Email();
    $email->address = 'test@example.com';
    $email->save();

    // Attach the email to the user as unverified
    $user->emails()->attach($email, [
        'is_primary' => true,
        'verified_at' => null,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Set the email as verified
    $response = $this->patch(route('admin.emails.set-verified', ['email' => $email->id]), [
        'user_id' => $user->id,
    ]);

    // Assert redirect to email edit page
    $response->assertRedirect(route('admin.emails.edit', ['email' => $email->id]));

    // Assert the email is now verified
    $emailUser = \DB::table('email_user')
        ->where('email_id', $email->id)
        ->where('user_id', $user->id)
        ->first();

    expect($emailUser->verified_at)->not->toBeNull();
});

test('admin can delete an email', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create an email
    $email = new Email();
    $email->address = 'test@example.com';
    $email->save();

    // Act as the admin
    $this->actingAs($admin);

    // Delete the email
    $response = $this->delete(route('admin.emails.destroy', ['email' => $email->id]));

    // Assert redirect to emails index
    $response->assertRedirect(route('admin.emails.index'));

    // Assert the email was deleted
    $this->assertDatabaseMissing('emails', [
        'id' => $email->id,
    ]);
});

test('non-admin users cannot manage emails', function () {
    // Create a regular user with member role
    $regularUser = User::factory()->create();
    $regularUser->email = 'regular@example.com'; // Set email to create the relationship
    $regularUser->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $regularUser->assignRole($memberRole);

    // Create an email
    $email = new Email();
    $email->address = 'test@example.com';
    $email->save();

    // Act as the regular user
    $this->actingAs($regularUser);

    // Try to access email management routes
    $routes = [
        'index' => $this->get(route('admin.emails.index')),
        'create' => $this->get(route('admin.emails.create')),
        'store' => $this->post(route('admin.emails.store'), [
            'address' => 'new@example.com',
            'user_id' => $regularUser->id,
        ]),
        'show' => $this->get(route('admin.emails.show', ['email' => $email->id])),
        'edit' => $this->get(route('admin.emails.edit', ['email' => $email->id])),
        'update' => $this->put(route('admin.emails.update', ['email' => $email->id]), [
            'address' => 'updated@example.com',
        ]),
        'destroy' => $this->delete(route('admin.emails.destroy', ['email' => $email->id])),
    ];

    // Assert all routes return forbidden status
    foreach ($routes as $route => $response) {
        $response->assertStatus(403);
    }
});
