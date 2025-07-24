<?php

namespace Database\Factories;

use App\Enum\AnnouncementType;
use App\Enum\Access;
use App\Enum\Role;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromDateTime = fake()->dateTimeBetween('-1 month', '+1 month');
        $toDateTime = fake()->dateTimeBetween($fromDateTime, '+2 months');

        return [
            'is_published' => fake()->boolean(70), // 70% chance of being published
            'from_datetime' => $fromDateTime,
            'to_datetime' => $toDateTime,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'type' => fake()->randomElement(AnnouncementType::cases())->value,
            'visible_to_access' => fake()->boolean(30) ? fake()->randomElements(
                array_map(fn($case) => $case->value, Access::cases()),
                fake()->numberBetween(1, 3)
            ) : null,
            'visible_to_role' => fake()->boolean(30) ? fake()->randomElements(
                array_map(fn($case) => $case->value, Role::cases()),
                fake()->numberBetween(1, 3)
            ) : null,
        ];
    }

    /**
     * Indicate that the announcement should be currently active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'from_datetime' => fake()->dateTimeBetween('-1 week', 'now'),
            'to_datetime' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that the announcement should be inactive (not published).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Indicate that the announcement should be expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'from_datetime' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'to_datetime' => fake()->dateTimeBetween('-1 month', '-1 week'),
        ]);
    }

    /**
     * Indicate that the announcement should be of a specific type.
     */
    public function ofType(AnnouncementType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type->value,
        ]);
    }

    /**
     * Indicate that the announcement should be urgent (danger type).
     */
    public function urgent(): static
    {
        return $this->ofType(AnnouncementType::DANGER);
    }

    /**
     * Indicate that the announcement should be informational.
     */
    public function info(): static
    {
        return $this->ofType(AnnouncementType::INFO);
    }
}
