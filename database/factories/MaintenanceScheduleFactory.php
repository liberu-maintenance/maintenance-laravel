<?php

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Checklist;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $frequencyType = fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly', 'hours']);
        $nextDueDate = fake()->dateTimeBetween('-1 month', '+2 months');

        return [
            'name' => fake()->words(3, true) . ' Maintenance',
            'description' => fake()->sentence(),
            'equipment_id' => Equipment::factory(),
            'frequency_type' => $frequencyType,
            'frequency_value' => fake()->numberBetween(1, 12),
            'next_due_date' => $nextDueDate,
            'last_completed_date' => fake()->optional()->dateTimeBetween('-3 months', '-1 day'),
            'estimated_duration' => fake()->numberBetween(15, 240),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['active', 'inactive', 'completed']),
            'instructions' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the maintenance schedule is active.
     * Indicate that the schedule is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the maintenance schedule is overdue.
     * Indicate that the schedule is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the maintenance schedule is due soon.
     */
    public function dueSoon(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => now()->addDays(fake()->numberBetween(1, $days)),
        ]);
    }

    /**
     * Indicate that the maintenance schedule is due on a specific date.
     */
    public function dueOn(\Carbon\Carbon|\DateTime|string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => $date,
        ]);
    }

    /**
     * Indicate that the maintenance schedule has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the maintenance schedule is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
        ]);
    }
}
