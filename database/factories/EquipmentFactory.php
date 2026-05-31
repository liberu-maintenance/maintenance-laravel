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
    #[\Override]
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'serial_number' => fake()->unique()->bothify('SN-####-????'),
            'model' => fake()->bothify('Model-###'),
            'manufacturer' => fake()->company(),
            'category' => fake()->randomElement([
                'HVAC',
                'Electrical',
                'Plumbing',
                'Mechanical',
                'IT Equipment',
                'Safety Equipment',
                'Vehicles',
            ]),
            'location' => fake()->randomElement([
                'Building A - Floor 1',
                'Building A - Floor 2',
                'Building B - Floor 1',
                'Warehouse',
                'Parking Lot',
            ]),
            'purchase_date' => fake()->dateTimeBetween('-5 years', '-1 year'),
            'warranty_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'status' => fake()->randomElement(['active', 'inactive', 'under_maintenance', 'retired']),
            'criticality' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'notes' => fake()->optional()->paragraph(),
            'sensor_enabled' => false,
            'sensor_type' => null,
            'sensor_id' => null,
            'sensor_config' => null,
            'last_sensor_reading_at' => null,
        ];
    }

    public function withSensor(): static
    {
        return $this->state(fn (array $attributes) => [
            'sensor_enabled' => true,
            'sensor_type' => fake()->randomElement([
                'temperature',
                'vibration',
                'pressure',
                'humidity',
                'power',
                'flow',
            ]),
            'sensor_id' => fake()->unique()->bothify('SENSOR-###-????'),
            'sensor_config' => [
                'thresholds' => [
                    'temperature' => [
                        'warning_min' => 20,
                        'warning_max' => 80,
                        'critical_min' => 10,
                        'critical_max' => 90,
                    ],
                ],
            ],
        ]);
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
     * Indicate that the equipment is under maintenance.
     */
    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_maintenance',
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
}
