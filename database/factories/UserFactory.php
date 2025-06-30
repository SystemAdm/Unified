<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'given_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
            'birthday' => fake()->date(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            // Get the primary email
            $primaryEmail = $user->emails()->wherePivot('is_primary', true)->first();

            if ($primaryEmail) {
                // Update the pivot to set verified_at to null
                $user->emails()->updateExistingPivot($primaryEmail->id, [
                    'verified_at' => null,
                ]);
            }
        });
    }
}
