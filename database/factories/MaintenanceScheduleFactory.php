<?php

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'frequency_type' => fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'frequency_value' => fake()->numberBetween(1, 4),
            'next_due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'last_completed_date' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'estimated_duration' => fake()->numberBetween(30, 480), // minutes
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => fake()->randomElement(['active', 'inactive']),
            'instructions' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the schedule is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the schedule is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => fake()->dateTimeBetween('-7 days', '-1 day'),
        ]);
    }
}
