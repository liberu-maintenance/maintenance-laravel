<?php

namespace Database\Factories;

use App\Models\InventoryPart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryPart>
 */
class InventoryPartFactory extends Factory
{
    protected $model = InventoryPart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_number' => strtoupper($this->faker->bothify('PART-####-???')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement([
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
            'unit_of_measure' => $this->faker->randomElement(['piece', 'box', 'liter', 'meter', 'kilogram']),
            'unit_cost' => $this->faker->randomFloat(2, 1, 500),
            'reorder_level' => $this->faker->numberBetween(5, 50),
            'reorder_quantity' => $this->faker->numberBetween(20, 200),
            'location' => $this->faker->randomElement(['Warehouse A', 'Warehouse B', 'Storage Room', 'Tool Crib']),
            'supplier' => $this->faker->company(),
            'lead_time_days' => $this->faker->numberBetween(1, 30),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
