<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    // Create a user with a birthday that makes them over 18 years old
    $user = User::factory()->create([
        'birthday' => now()->subYears(20)->format('Y-m-d'), // 20 years old
    ]);

    // Ensure the user has a verified email
    $user->markEmailAsVerified();

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
