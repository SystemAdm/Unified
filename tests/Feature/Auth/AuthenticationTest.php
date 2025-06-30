<?php

use App\Models\User;
use App\Models\Email;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('if a email exists with single user exists, render password screen', function () {
    // Create a user with an email
    $user = User::factory()->create();
    $emailModel = Email::factory()->create(['address' => 'user1@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true]);

    // Submit the login form with the email
    $response = $this->post('/login', [
        'email' => 'user1@example.com',
    ]);

    // Assert that we're redirected to the password screen for this user
    $response->assertRedirect(route('login.password', ['user_id' => $user->id, 'remember' => 0]));
});

test('if a email exists with multiple users exists, render chooseuser screen', function () {
    // Create an email
    $emailModel = Email::factory()->create(['address' => 'shared@example.com']);

    // Create two users and associate them with the same email
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->emails()->attach($emailModel, ['is_primary' => true]);
    $user2->emails()->attach($emailModel, ['is_primary' => true]);

    // Submit the login form with the shared email
    $response = $this->post('/login', [
        'email' => 'shared@example.com',
    ]);

    // Assert that we're redirected to the choose user screen
    $response->assertRedirect(route('login.choose-user', ['email_id' => $emailModel->id]));
});

test('if a email exists with multiple users exists, user choosen, render password screen', function () {
    // Create an email
    $emailModel = Email::factory()->create(['address' => 'shared@example.com']);

    // Create two users and associate them with the same email
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->emails()->attach($emailModel, ['is_primary' => true]);
    $user2->emails()->attach($emailModel, ['is_primary' => true]);

    // Visit the choose user page
    $response = $this->get(route('login.choose-user', ['email_id' => $emailModel->id]));
    $response->assertStatus(200);

    // Choose the first user (this would normally be a form submission in the UI)
    $response = $this->get(route('login.password', ['user_id' => $user1->id]));

    // Assert that we're shown the password screen for the chosen user
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('auth/LoginPassword')
             ->has('user')
             ->where('user.id', $user1->id)
    );
});

test('authenticated through password', function () {
    $user = User::factory()->create();
    $emailModel = Email::factory()->create(['address' => 'password-auth@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true]);

    // First, visit the password screen for this user
    $this->get(route('login.password', ['user_id' => $user->id]));

    // Then submit the password
    $response = $this->post(route('login.authenticate-with-password'), [
        'user_id' => $user->id,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $emailModel = Email::factory()->create(['address' => 'auth-user@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true]);

    $response = $this->post('/login', [
        'email' => 'auth-user@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();
    $emailModel = Email::factory()->create(['address' => 'invalid-pw@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true]);

    $this->post('/login', [
        'email' => 'invalid-pw@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    $emailModel = Email::factory()->create(['address' => 'logout-user@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true]);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('unverified users are redirected to verification notice after login with password', function () {
    $user = User::factory()->unverified()->create();
    $emailModel = Email::factory()->create(['address' => 'unverified-user@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true, 'verified_at' => null]);

    // Try to authenticate with password
    $response = $this->post(route('login.authenticate-with-password'), [
        'user_id' => $user->id,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});

test('unverified users are redirected to verification notice after direct login', function () {
    $user = User::factory()->unverified()->create();
    $emailModel = Email::factory()->create(['address' => 'unverified-direct@example.com']);
    $user->emails()->attach($emailModel, ['is_primary' => true, 'verified_at' => null]);

    // Try to authenticate directly
    $response = $this->post('/login', [
        'email' => 'unverified-direct@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});

test('unverified users are redirected to verification notice after chosen user login', function () {
    // Create an email
    $emailModel = Email::factory()->create(['address' => 'unverified-chosen@example.com']);

    // Create a user and associate with the email
    $user = User::factory()->unverified()->create();
    $user->emails()->attach($emailModel, ['is_primary' => true, 'verified_at' => null]);

    // Try to authenticate as chosen user
    $response = $this->post(route('login.authenticate-chosen-user', ['email_id' => $emailModel->id]), [
        'user_id' => $user->id,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});
