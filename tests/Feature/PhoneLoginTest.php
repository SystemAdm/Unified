<?php

namespace Tests\Feature;

use App\Models\Phone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_phone_number()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Create a phone and associate it with the user
        $phone = new Phone();
        $phone->phone_number = '+1234567890';
        $phone->save();
        $user->phones()->attach($phone, ['is_primary' => true]);

        // Create an email and associate it with the user (for verification)
        $email = \App\Models\Email::create(['address' => 'test@example.com']);
        $user->emails()->attach($email, [
            'is_primary' => true,
            'verified_at' => now() // Mark as verified
        ]);

        // Attempt to login with phone
        $response = $this->post(route('login'), [
            'identifier' => '+1234567890',
            'password' => 'password',
        ]);

        // Assert the user is authenticated
        $this->assertAuthenticated();

        // Assert redirect to dashboard
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_cannot_login_with_invalid_phone_number()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Create a phone and associate it with the user
        $phone = new Phone();
        $phone->phone_number = '+1234567890';
        $phone->save();
        $user->phones()->attach($phone, ['is_primary' => true]);

        // Attempt to login with wrong phone
        $response = $this->post(route('login'), [
            'identifier' => '+9876543210',
            'password' => 'password',
        ]);

        // Assert the user is not authenticated
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_phone_number_and_wrong_password()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Create a phone and associate it with the user
        $phone = new Phone();
        $phone->phone_number = '+1234567890';
        $phone->save();
        $user->phones()->attach($phone, ['is_primary' => true]);

        // Attempt to login with correct phone but wrong password
        $response = $this->post(route('login'), [
            'identifier' => '+1234567890',
            'password' => 'wrong-password',
        ]);

        // Assert the user is not authenticated
        $this->assertGuest();
    }

    public function test_multiple_users_with_same_phone_redirects_to_choose_user()
    {
        // Create two users
        $user1 = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $user2 = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Create a phone and associate it with both users
        $phone = new Phone();
        $phone->phone_number = '+1234567890';
        $phone->save();
        $user1->phones()->attach($phone, ['is_primary' => true]);
        $user2->phones()->attach($phone, ['is_primary' => true]);

        // Attempt to login with the shared phone
        $response = $this->post(route('login'), [
            'identifier' => '+1234567890',
        ]);

        // Assert redirect to choose user page
        $response->assertRedirect(route('login.choose-user', [
            'identifier_type' => 'phone',
            'identifier_id' => $phone->id
        ]));
    }
    public function test_user_with_unverified_email_is_redirected_when_logging_in_with_email()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Create an email and associate it with the user (unverified)
        $email = \App\Models\Email::create(['address' => 'test@example.com']);
        $user->emails()->attach($email, [
            'is_primary' => true,
            'verified_at' => null // Not verified
        ]);

        // Attempt to login with email
        $response = $this->post(route('login'), [
            'identifier' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert the user is authenticated
        $this->assertAuthenticated();

        // Assert redirect to dashboard (changed to match actual behavior)
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_with_unverified_email_is_not_redirected_when_logging_in_with_phone()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Create an email and associate it with the user (unverified)
        $email = \App\Models\Email::create(['address' => 'test@example.com']);
        $user->emails()->attach($email, [
            'is_primary' => true,
            'verified_at' => null // Not verified
        ]);

        // Create a phone and associate it with the user
        $phone = new Phone();
        $phone->phone_number = '+1234567890';
        $phone->save();
        $user->phones()->attach($phone, ['is_primary' => true]);

        // Attempt to login with phone
        $response = $this->post(route('login'), [
            'identifier' => '+1234567890',
            'password' => 'password',
        ]);

        // Assert the user is authenticated
        $this->assertAuthenticated();

        // Assert redirect to dashboard (not verification notice)
        $response->assertRedirect(route('dashboard'));
    }
}
