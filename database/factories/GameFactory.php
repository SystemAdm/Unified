<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $consoles = ['PC', 'PlayStation', 'Xbox', 'Nintendo Switch', 'Mobile'];

        return [
            'name' => fake()->unique()->words(2, true),
            'version' => fake()->semver(),
            'console' => fake()->randomElement($consoles),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }
}
