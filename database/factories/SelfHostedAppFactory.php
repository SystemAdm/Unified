<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SelfHostedApp>
 */
class SelfHostedAppFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' App',
            'description' => $this->faker->paragraph(),
            'public_link' => $this->faker->url(),
            'admin_link' => $this->faker->url(),
            'demo_username' => $this->faker->userName(),
            'demo_password' => $this->faker->password(8, 12),
            'image' => $this->faker->randomElement([
                'selfhosted/app1.jpg',
                'selfhosted/app2.jpg',
                'selfhosted/app3.jpg',
                null
            ]),
            'status' => $this->faker->randomElement(['draft', 'published']),
            'visibility' => $this->faker->randomElement(['admin', 'user', 'guest']),
        ];
    }

    /**
     * Indicate that the app is published.
     *
     * @return static
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the app is in draft status.
     *
     * @return static
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
