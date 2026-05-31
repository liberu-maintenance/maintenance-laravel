<?php

namespace Database\Factories;

use App\Models\IotSensorReading;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IotSensorReading>
 */
class IotSensorReadingFactory extends Factory
{
    #[\Override]
    protected $model = IotSensorReading::class;

    public function definition(): array
    {
        $sensorType = fake()->randomElement([
            'temperature',
            'vibration',
            'pressure',
            'humidity',
            'power',
            'flow',
        ]);

        $metricConfig = [
            'temperature' => ['value' => fake()->numberBetween(20, 90), 'unit' => '°C'],
            'vibration' => ['value' => fake()->randomFloat(2, 0, 10), 'unit' => 'mm/s'],
            'pressure' => ['value' => fake()->numberBetween(1, 100), 'unit' => 'PSI'],
            'humidity' => ['value' => fake()->numberBetween(20, 80), 'unit' => '%'],
            'power' => ['value' => fake()->numberBetween(100, 5000), 'unit' => 'W'],
            'flow' => ['value' => fake()->randomFloat(2, 0, 100), 'unit' => 'L/min'],
        ];

        $config = $metricConfig[$sensorType];

        return [
            'equipment_id' => Equipment::factory(),
            'sensor_type' => $sensorType,
            'metric_name' => $sensorType,
            'value' => $config['value'],
            'unit' => $config['unit'],
            'metadata' => [
                'battery_level' => fake()->numberBetween(50, 100) . '%',
                'signal_strength' => fake()->randomElement(['excellent', 'good', 'fair', 'poor']),
            ],
            'status' => fake()->randomElement(['normal', 'warning', 'critical']),
            'reading_time' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function normal(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'normal',
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'warning',
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'critical',
        ]);
    }
}
