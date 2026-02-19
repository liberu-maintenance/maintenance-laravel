<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Company;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'serial_number' => $this->faker->unique()->bothify('SN-####-????'),
            'model' => $this->faker->bothify('Model-###'),
            'manufacturer' => $this->faker->company(),
            'category' => $this->faker->randomElement([
                'HVAC',
                'Electrical',
                'Plumbing',
                'Mechanical',
                'IT Equipment',
                'Safety Equipment',
                'Vehicles',
            ]),
            'location' => $this->faker->randomElement([
                'Building A - Floor 1',
                'Building A - Floor 2',
                'Building B - Floor 1',
                'Warehouse',
                'Parking Lot',
            ]),
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'warranty_expiry' => $this->faker->dateTimeBetween('now', '+2 years'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'under_maintenance', 'retired']),
            'criticality' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'notes' => $this->faker->optional()->paragraph(),
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
            'sensor_type' => $this->faker->randomElement([
                'temperature',
                'vibration',
                'pressure',
                'humidity',
                'power',
                'flow',
            ]),
            'sensor_id' => $this->faker->unique()->bothify('SENSOR-###-????'),
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
}
