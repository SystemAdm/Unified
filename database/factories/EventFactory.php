<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+2 months');
        $endDate = clone $startDate;
        $endDate->modify('+' . rand(1, 8) . ' hours');

        // Signup dates (if has_signup is true)
        $hasSignup = $this->faker->boolean(60); // 60% chance of having signup
        $signupStartDate = null;
        $signupEndDate = null;

        if ($hasSignup) {
            $signupStartDate = clone $startDate;
            $signupStartDate->modify('-' . rand(7, 30) . ' days');

            $signupEndDate = clone $startDate;
            $signupEndDate->modify('-' . rand(1, 6) . ' days');
        }

        // Cancellation (if is_cancelled is true)
        $isCancelled = $this->faker->boolean(5); // 10% chance of being cancelled
        $cancelledAt = null;
        $cancellationReason = null;

        if ($isCancelled) {
            $cancelledAt = clone $startDate;
            $cancelledAt->modify('-' . rand(1, 5) . ' days');
            $cancellationReason = $this->faker->sentence();
        }

        return [
            'title' => $this->faker->sentence(rand(3, 8)),
            'description' => $this->faker->paragraphs(rand(1, 3), true),
            'start_date' => $startDate,
            'end_date' => $endDate,

            // Signup options
            'has_signup' => $hasSignup,
            'signup_start_date' => $signupStartDate,
            'signup_end_date' => $signupEndDate,

            // Number of seats
            'seats' => $this->faker->randomElement([null, 10, 20, 50]),

            // Location
            'location_id' => 1,

            // Limits
            'min_age' => 16,
            'max_age' => null,
            //'class_restriction' => $this->faker->boolean(20) ? $this->faker->randomElement(['beginner', 'intermediate', 'advanced']) : null,

            // Restriction
            'restriction' => 'everyone',//$this->faker->randomElement(['everyone', 'members', 'crew']),

            // Cancellation
            'is_cancelled' => $isCancelled,
            'cancelled_at' => $cancelledAt,
            'cancellation_reason' => $cancellationReason,

            'status' => $isCancelled ? 'cancelled':'published',
        ];
    }
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Event $event) {
            // Randomly attach organizers (users)
            if ($this->faker->boolean(10)) { // 10% chance of having organizers
                $users = \App\Models\User::inRandomOrder()->limit(rand(1, 3))->get();
                foreach ($users as $user) {
                    $event->users()->attach($user);
                }
            }

            // Randomly attach organizations
            if ($this->faker->boolean(100)) { // 90% chance of having organizations
                $organizations = \App\Models\Organization::find(1);
                if ($organizations->count() > 0) {
                    $event->organizations()->attach($organizations);
                }
            }

            // Randomly attach signupped users
            if ($event->has_signup && $this->faker->boolean(70)) { // 70% chance of having signupped users if signup is enabled
                $signupCount = $event->seats ? min(rand(1, 10), $event->seats) : rand(1, 10);
                $users = \App\Models\User::inRandomOrder()->limit($signupCount)->get();
                foreach ($users as $user) {
                    $event->signupped()->attach($user);
                }
            }

            // Randomly attach registered users
            if ($this->faker->boolean(50)) { // 50% chance of having registered users
                $registeredCount = rand(1, 5);
                $users = \App\Models\User::inRandomOrder()->limit($registeredCount)->get();
                foreach ($users as $user) {
                    $event->registered()->attach($user);
                }
            }

            // Randomly attach attending users
            if ($this->faker->boolean(30)) { // 30% chance of having attending users
                $attendingCount = rand(1, 5);
                $users = \App\Models\User::inRandomOrder()->limit($attendingCount)->get();
                foreach ($users as $user) {
                    $event->attending()->attach($user);
                }
            }
        });
    }
}
