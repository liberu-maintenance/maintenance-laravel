<?php

namespace Database\Factories;

use App\Models\InventoryPart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryPart>
 */
class InventoryPartFactory extends Factory
{
    #[\Override]
    protected $model = InventoryPart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_number' => strtoupper(fake()->bothify('PART-####-???')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement([
                'Mechanical',
                'Electrical',
                'Hydraulic',
                'Pneumatic',
                'Consumables',
                'Fasteners',
                'Filters',
                'Lubricants',
                'Safety',
                'Other',
            ]),
            'unit_of_measure' => fake()->randomElement(['piece', 'box', 'liter', 'meter', 'kilogram']),
            'unit_cost' => fake()->randomFloat(2, 1, 500),
            'reorder_level' => fake()->numberBetween(5, 50),
            'reorder_quantity' => fake()->numberBetween(20, 200),
            'location' => fake()->randomElement(['Warehouse A', 'Warehouse B', 'Storage Room', 'Tool Crib']),
            'supplier' => fake()->company(),
            'lead_time_days' => fake()->numberBetween(1, 30),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
