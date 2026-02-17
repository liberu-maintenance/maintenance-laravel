<?php

namespace Database\Factories;

use App\Models\Checklist;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checklist>
 */
class ChecklistFactory extends Factory
{
    protected $model = Checklist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'category' => fake()->randomElement(['Preventive', 'Corrective', 'Inspection', 'Safety', 'Other']),
            'is_template' => fake()->boolean(30),
            'status' => fake()->randomElement(['active', 'inactive', 'draft']),
        ];
    }

    /**
     * Indicate that the checklist is a template.
     */
    public function template(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_template' => true,
        ]);
    }

    /**
     * Indicate that the checklist is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }
}
