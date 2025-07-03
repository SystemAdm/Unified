<?php

namespace Database\Factories;

use App\Models\Email;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

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
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Create an email for the user if it doesn't have one
            if (!$user->email) {
                $email = Email::factory()->create();
                $user->emails()->attach($email->id, [
                    'is_primary' => true,
                    'verified_at' => now()
                ]);
            }
        });
    }

    /**
     * Indicate that the user's email should be unverified.
     */
    public function unverified(): static
    {
        return $this->afterCreating(function (User $user) {
            // Create an email for the user if it doesn't have one
            if (!$user->email) {
                $email = Email::factory()->create();
                $user->emails()->attach($email->id, [
                    'is_primary' => true,
                    'verified_at' => null
                ]);
            } else {
                // If the user already has an email, make sure it's unverified
                $primaryEmail = $user->getPrimaryEmail();
                if ($primaryEmail) {
                    $user->emails()->updateExistingPivot($primaryEmail->id, [
                        'verified_at' => null
                    ]);
                }
            }
        });
    }

    public function withoutPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }

}
