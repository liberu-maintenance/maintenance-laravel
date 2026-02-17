<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Company;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

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
            'serial_number' => fake()->unique()->bothify('SN-####-????'),
            'model' => fake()->word() . ' ' . fake()->numberBetween(100, 9999),
            'manufacturer' => fake()->company(),
            'category' => fake()->randomElement(['HVAC', 'Electrical', 'Plumbing', 'Mechanical', 'IT', 'Other']),
            'location' => fake()->optional()->address(),
            'purchase_date' => fake()->optional()->dateTimeBetween('-10 years', '-1 year'),
            'warranty_expiry' => fake()->optional()->dateTimeBetween('now', '+5 years'),
            'status' => fake()->randomElement(['active', 'inactive', 'under_maintenance', 'retired']),
            'criticality' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the equipment is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the equipment is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'criticality' => 'critical',
        ]);
    }

    /**
     * Indicate that the equipment is under maintenance.
     */
    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_maintenance',
        ]);
    }
}
