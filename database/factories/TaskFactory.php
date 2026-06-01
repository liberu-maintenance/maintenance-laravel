<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'task_id'     => fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->unique()->sentence(),
            'due_date'    => fake()->dateTimeBetween('now', '+30 days'),
            'status'      => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'priority'    => fake()->numberBetween(1, 5),
        ];
    }
}
