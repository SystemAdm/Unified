<?php

namespace Tests\Feature\Admin;

use App\Enum\Role;
use App\Models\Phone;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    // Seed the roles and permissions
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can create a phone', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a user to associate with the phone
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Act as the admin
    $this->actingAs($admin);

    // Create a new phone
    $response = $this->post(route('admin.phones.store'), [
        'phone_number' => '+4712345678',
        'user_id' => $user->id,
        'is_primary' => true,
        'is_verified' => true,
    ]);

    // Assert redirect to phones index
    $response->assertRedirect(route('admin.phones.index'));

    // Assert the phone was created
    $this->assertDatabaseHas('phones', [
        'country_code' => '47',
        'number' => '12345678',
    ]);

    // Get the created phone
    $phone = Phone::where('country_code', '47')->where('number', '12345678')->first();

    // Assert the phone was associated with the user
    $this->assertDatabaseHas('phone_user', [
        'phone_id' => $phone->id,
        'user_id' => $user->id,
        'is_primary' => 1,
    ]);

    // Assert the phone has a verified_at timestamp
    $phoneUser = \DB::table('phone_user')
        ->where('phone_id', $phone->id)
        ->where('user_id', $user->id)
        ->first();

    expect($phoneUser->verified_at)->not->toBeNull();
});

test('admin can update a phone', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a phone to be updated
    $phone = new Phone();
    $phone->phone_number = '+4712345678';
    $phone->save();

    // Act as the admin
    $this->actingAs($admin);

    // Update the phone
    $response = $this->put(route('admin.phones.update', ['phone' => $phone->id]), [
        'phone_number' => '+4787654321',
    ]);

    // Assert redirect to phones index
    $response->assertRedirect(route('admin.phones.index'));

    // Refresh the phone from the database
    $phone->refresh();

    // Assert the phone was updated
    expect($phone->country_code)->toBe('47');
    expect($phone->number)->toBe('87654321');
    expect($phone->phone_number)->toBe('+4787654321');
});

test('admin can attach a user to a phone', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a phone
    $phone = new Phone();
    $phone->phone_number = '+4712345678';
    $phone->save();

    // Create a user to attach
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Act as the admin
    $this->actingAs($admin);

    // Attach the user to the phone
    $response = $this->post(route('admin.phones.attach-user', ['phone' => $phone->id]), [
        'user_id' => $user->id,
        'is_primary' => true,
        'is_verified' => true,
    ]);

    // Assert redirect to phone edit page
    $response->assertRedirect(route('admin.phones.edit', ['phone' => $phone->id]));

    // Assert the user was attached to the phone
    $this->assertDatabaseHas('phone_user', [
        'phone_id' => $phone->id,
        'user_id' => $user->id,
        'is_primary' => 1,
    ]);

    // Assert the phone has a verified_at timestamp
    $phoneUser = \DB::table('phone_user')
        ->where('phone_id', $phone->id)
        ->where('user_id', $user->id)
        ->first();

    expect($phoneUser->verified_at)->not->toBeNull();
});

test('admin can detach a user from a phone', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a phone
    $phone = new Phone();
    $phone->phone_number = '+4712345678';
    $phone->save();

    // Create a user to attach and then detach
    $user = User::factory()->create();
    $user->email = 'user@example.com'; // Set email to create the relationship
    $user->save();

    // Attach the user to the phone
    $user->phones()->attach($phone, [
        'is_primary' => true,
        'verified_at' => now(),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Detach the user from the phone
    $response = $this->delete(route('admin.phones.detach-user', ['phone' => $phone->id]), [
        'user_id' => $user->id,
    ]);

    // Assert redirect to phone edit page
    $response->assertRedirect(route('admin.phones.edit', ['phone' => $phone->id]));

    // Assert the user was detached from the phone
    $this->assertDatabaseMissing('phone_user', [
        'phone_id' => $phone->id,
        'user_id' => $user->id,
    ]);
});

test('admin can set a phone as primary for a user', function () {
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

    // Create two phones
    $phone1 = new Phone();
    $phone1->phone_number = '+4712345678';
    $phone1->save();

    $phone2 = new Phone();
    $phone2->phone_number = '+4787654321';
    $phone2->save();

    // Attach both phones to the user, with phone1 as primary
    $user->phones()->attach($phone1, [
        'is_primary' => true,
        'verified_at' => now(),
    ]);

    $user->phones()->attach($phone2, [
        'is_primary' => false,
        'verified_at' => now(),
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Set phone2 as primary
    $response = $this->patch(route('admin.phones.set-primary', ['phone' => $phone2->id]), [
        'user_id' => $user->id,
    ]);

    // Assert redirect to phone edit page
    $response->assertRedirect(route('admin.phones.edit', ['phone' => $phone2->id]));

    // Assert phone2 is now primary
    $this->assertDatabaseHas('phone_user', [
        'phone_id' => $phone2->id,
        'user_id' => $user->id,
        'is_primary' => 1,
    ]);

    // Assert phone1 is no longer primary
    $this->assertDatabaseHas('phone_user', [
        'phone_id' => $phone1->id,
        'user_id' => $user->id,
        'is_primary' => 0,
    ]);
});

test('admin can set a phone as verified for a user', function () {
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

    // Create a phone
    $phone = new Phone();
    $phone->phone_number = '+4712345678';
    $phone->save();

    // Attach the phone to the user as unverified
    $user->phones()->attach($phone, [
        'is_primary' => true,
        'verified_at' => null,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Set the phone as verified
    $response = $this->patch(route('admin.phones.set-verified', ['phone' => $phone->id]), [
        'user_id' => $user->id,
    ]);

    // Assert redirect to phone edit page
    $response->assertRedirect(route('admin.phones.edit', ['phone' => $phone->id]));

    // Assert the phone is now verified
    $phoneUser = \DB::table('phone_user')
        ->where('phone_id', $phone->id)
        ->where('user_id', $user->id)
        ->first();

    expect($phoneUser->verified_at)->not->toBeNull();
});

test('admin can delete a phone', function () {
    // Create an admin user
    $admin = User::factory()->create();
    $admin->email = 'admin@example.com'; // Set email to create the relationship
    $admin->save();

    $adminRole = RoleModel::findByName(Role::ADMIN->value);
    $admin->assignRole($adminRole);

    // Create a phone
    $phone = new Phone();
    $phone->phone_number = '+4712345678';
    $phone->save();

    // Act as the admin
    $this->actingAs($admin);

    // Delete the phone
    $response = $this->delete(route('admin.phones.destroy', ['phone' => $phone->id]));

    // Assert redirect to phones index
    $response->assertRedirect(route('admin.phones.index'));

    // Assert the phone was deleted
    $this->assertDatabaseMissing('phones', [
        'id' => $phone->id,
    ]);
});

test('non-admin users cannot manage phones', function () {
    // Create a regular user with member role
    $regularUser = User::factory()->create();
    $regularUser->email = 'regular@example.com'; // Set email to create the relationship
    $regularUser->save();

    $memberRole = RoleModel::findByName(Role::MEMBER->value);
    $regularUser->assignRole($memberRole);

    // Create a phone
    $phone = new Phone();
    $phone->phone_number = '+4712345678';
    $phone->save();

    // Act as the regular user
    $this->actingAs($regularUser);

    // Try to access phone management routes
    $routes = [
        'index' => $this->get(route('admin.phones.index')),
        'create' => $this->get(route('admin.phones.create')),
        'store' => $this->post(route('admin.phones.store'), [
            'phone_number' => '+4787654321',
            'user_id' => $regularUser->id,
        ]),
        'show' => $this->get(route('admin.phones.show', ['phone' => $phone->id])),
        'edit' => $this->get(route('admin.phones.edit', ['phone' => $phone->id])),
        'update' => $this->put(route('admin.phones.update', ['phone' => $phone->id]), [
            'phone_number' => '+4787654321',
        ]),
        'destroy' => $this->delete(route('admin.phones.destroy', ['phone' => $phone->id])),
    ];

    // Assert all routes return forbidden status
    foreach ($routes as $route => $response) {
        $response->assertStatus(403);
    }
});
