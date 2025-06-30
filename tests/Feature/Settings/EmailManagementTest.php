<?php

use App\Models\User;
use App\Models\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('email settings page is displayed', function () {
    $user = User::factory()->create();

    // Create an email for the user
    $email = Email::factory()->create(['address' => 'test@example.com']);
    $user->emails()->attach($email, ['is_primary' => true, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->get('/settings/email');

    $response->assertOk();
});

test('user can add a new email', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->post('/settings/emails', [
            'address' => 'new@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/email');

    // Check that the new email was added
    expect($user->emails()->where('address', 'new@example.com')->exists())->toBeTrue();

    // Check that the new email is not primary
    $newEmail = $user->emails()->where('address', 'new@example.com')->first();
    expect($newEmail->pivot->is_primary == 1)->toBeFalse();
    expect($newEmail->pivot->verified_at)->toBeNull();
});

test('user can set an email as primary', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    // Create a secondary email for the user
    $secondaryEmail = Email::factory()->create(['address' => 'secondary@example.com']);
    $user->emails()->attach($secondaryEmail, ['is_primary' => false, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch("/settings/emails/{$secondaryEmail->id}/primary");

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/email');

    // Refresh the user to get updated pivot data
    $user->refresh();

    // Check that the secondary email is now primary
    $updatedSecondaryEmail = $user->emails()->where('address', 'secondary@example.com')->first();
    expect($updatedSecondaryEmail->pivot->is_primary == 1)->toBeTrue();

    // Check that the previously primary email is no longer primary
    $updatedPrimaryEmail = $user->emails()->where('address', 'primary@example.com')->first();
    expect($updatedPrimaryEmail->pivot->is_primary == 1)->toBeFalse();
});

test('user can request email verification', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    // Create an unverified email for the user
    $unverifiedEmail = Email::factory()->create(['address' => 'unverified@example.com']);
    $user->emails()->attach($unverifiedEmail, ['is_primary' => false, 'verified_at' => null]);

    // Mock the notification
    \Illuminate\Support\Facades\Notification::fake();

    $response = $this
        ->actingAs($user)
        ->post("/settings/emails/{$unverifiedEmail->id}/verify");

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/email')
        ->assertSessionHas('status', 'verification-link-sent');

    // Assert that a notification was sent to the user
    \Illuminate\Support\Facades\Notification::assertSentTo(
        $user,
        \App\Notifications\VerifyAdditionalEmail::class,
        function ($notification, $channels, $notifiable) use ($unverifiedEmail) {
            return $notification->getEmail()->id === $unverifiedEmail->id;
        }
    );
});

test('user can verify an email with a verification link', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    // Create an unverified email for the user
    $unverifiedEmail = Email::factory()->create(['address' => 'unverified@example.com']);
    $user->emails()->attach($unverifiedEmail, ['is_primary' => false, 'verified_at' => null]);

    // Create a signed URL
    $url = URL::temporarySignedRoute(
        'emails.verify',
        Carbon::now()->addMinutes(60),
        [
            'id' => $user->id,
            'email_id' => $unverifiedEmail->id,
            'hash' => sha1($unverifiedEmail->address),
        ]
    );

    $response = $this
        ->actingAs($user)
        ->get($url);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/email')
        ->assertSessionHas('status', 'email-verified');

    // Refresh the user to get updated pivot data
    $user->refresh();

    // Check that the email is now verified
    $updatedEmail = $user->emails()->where('address', 'unverified@example.com')->first();
    expect($updatedEmail->pivot->verified_at)->not->toBeNull();
});

test('user can update a non-primary email', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    // Create a secondary email for the user
    $secondaryEmail = Email::factory()->create(['address' => 'secondary@example.com']);
    $user->emails()->attach($secondaryEmail, ['is_primary' => false, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch("/settings/emails/{$secondaryEmail->id}", [
            'address' => 'updated@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/email');

    // Check that the old email is detached
    expect($user->emails()->where('address', 'secondary@example.com')->exists())->toBeFalse();

    // Check that the new email is attached
    expect($user->emails()->where('address', 'updated@example.com')->exists())->toBeTrue();

    // Check that the new email is not primary and not verified
    $updatedEmail = $user->emails()->where('address', 'updated@example.com')->first();
    expect($updatedEmail->pivot->is_primary == 1)->toBeFalse();
    expect($updatedEmail->pivot->verified_at)->toBeNull();
});

test('user cannot update a primary email', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch("/settings/emails/{$primaryEmail->id}", [
            'address' => 'updated@example.com',
        ]);

    $response->assertStatus(403);

    // Check that the primary email is unchanged
    expect($user->emails()->where('address', 'primary@example.com')->exists())->toBeTrue();
});

test('user can delete a non-primary email', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    // Create a secondary email for the user
    $secondaryEmail = Email::factory()->create(['address' => 'secondary@example.com']);
    $user->emails()->attach($secondaryEmail, ['is_primary' => false, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->delete("/settings/emails/{$secondaryEmail->id}");

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/email');

    // Check that the secondary email is detached
    expect($user->emails()->where('address', 'secondary@example.com')->exists())->toBeFalse();

    // Check that the primary email still exists
    expect($user->emails()->where('address', 'primary@example.com')->exists())->toBeTrue();
});

test('user cannot delete a primary email', function () {
    $user = User::factory()->create();

    // Create a primary email for the user
    $primaryEmail = Email::factory()->create(['address' => 'primary@example.com']);
    $user->emails()->attach($primaryEmail, ['is_primary' => true, 'verified_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->delete("/settings/emails/{$primaryEmail->id}");

    $response->assertStatus(403);

    // Check that the primary email still exists
    expect($user->emails()->where('address', 'primary@example.com')->exists())->toBeTrue();
});

test('user cannot access another users email', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create an email for user2
    $email = Email::factory()->create(['address' => 'user2@example.com']);
    $user2->emails()->attach($email, ['is_primary' => true, 'verified_at' => now()]);

    // Try to access user2's email as user1
    $response = $this
        ->actingAs($user1)
        ->patch("/settings/emails/{$email->id}/primary");

    $response->assertStatus(403);

    // Try to update user2's email as user1
    $response = $this
        ->actingAs($user1)
        ->patch("/settings/emails/{$email->id}", [
            'address' => 'updated@example.com',
        ]);

    $response->assertStatus(403);

    // Try to delete user2's email as user1
    $response = $this
        ->actingAs($user1)
        ->delete("/settings/emails/{$email->id}");

    $response->assertStatus(403);
});
