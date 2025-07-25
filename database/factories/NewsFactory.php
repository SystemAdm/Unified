<?php

namespace Database\Factories;

use App\Enum\Access;
use App\Enum\Role;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_published' => fake()->boolean(80), // 80% chance of being published
            'published_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'title' => fake()->sentence(6),
            'excerpt' => fake()->boolean(80) ? fake()->paragraph(2) : null,
            'content' => fake()->paragraphs(fake()->numberBetween(3, 8), true),
            'author_id' => fake()->boolean(70) ? 1 : null, // Default to user ID 1 or null
            'featured_image' => fake()->boolean(60) ? 'news/4EWPQDydTCBR22wNWUdasX5jnMrAhImnJZSJkxdH.png' : null,
            'visible_to_role' => fake()->boolean(20) ? fake()->randomElements(
                array_map(fn($case) => $case->value, Role::cases()),
                fake()->numberBetween(1, 3)
            ) : null,
        ];
    }

    /**
     * Indicate that the news should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the news should be a draft (not published).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the news should be scheduled for future publication.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that the news should have a featured image.
     */
    public function withFeaturedImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_image' => 'news/4EWPQDydTCBR22wNWUdasX5jnMrAhImnJZSJkxdH.png',
        ]);
    }

    /**
     * Indicate that the news should not have a featured image.
     */
    public function withoutFeaturedImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_image' => null,
        ]);
    }

    /**
     * Indicate that the news should have an author.
     */
    public function withAuthor(int $authorId = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $authorId,
        ]);
    }

    /**
     * Indicate that the news should not have an author.
     */
    public function withoutAuthor(): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => null,
        ]);
    }

    /**
     * Indicate that the news should have an excerpt.
     */
    public function withExcerpt(): static
    {
        return $this->state(fn (array $attributes) => [
            'excerpt' => fake()->paragraph(2),
        ]);
    }

    /**
     * Indicate that the news should not have an excerpt.
     */
    public function withoutExcerpt(): static
    {
        return $this->state(fn (array $attributes) => [
            'excerpt' => null,
        ]);
    }

    /**
     * Indicate that the news should be a long article.
     */
    public function longArticle(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(fake()->numberBetween(10, 20), true),
            'excerpt' => fake()->paragraph(3),
        ]);
    }

    /**
     * Indicate that the news should be a short article.
     */
    public function shortArticle(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(fake()->numberBetween(2, 4), true),
            'excerpt' => fake()->paragraph(1),
        ]);
    }
}
