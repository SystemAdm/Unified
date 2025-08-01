<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameServer>
 */
class GameServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true) . ' Server',
            'ip_address' => fake()->ipv4(),
            'port' => fake()->numberBetween(1000, 65535),
            'game_id' => \App\Models\Game::factory(),
            'description' => fake()->paragraph(),
            'max_players' => fake()->numberBetween(10, 100),
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }
}
