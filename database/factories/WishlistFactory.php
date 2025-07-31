<?php

namespace Database\Factories;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wishlist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'image' => $this->faker->imageUrl(640, 480, 'products', true),
            'description' => $this->faker->paragraph(),
            'link' => $this->faker->url(),
            'cost_per_unit' => $this->faker->randomFloat(2, 10, 1000),
            'count' => $this->faker->numberBetween(1, 10),
            'deadline' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
        ];
    }
}
