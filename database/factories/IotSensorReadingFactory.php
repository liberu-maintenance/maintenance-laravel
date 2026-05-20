<?php

namespace Database\Factories;

use App\Models\IotSensorReading;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class IotSensorReadingFactory extends Factory
{
    protected $model = IotSensorReading::class;

    public function definition(): array
    {
        $sensorType = $this->faker->randomElement([
            'temperature',
            'vibration',
            'pressure',
            'humidity',
            'power',
            'flow',
        ]);

        $metricConfig = [
            'temperature' => ['value' => $this->faker->numberBetween(20, 90), 'unit' => '°C'],
            'vibration' => ['value' => $this->faker->randomFloat(2, 0, 10), 'unit' => 'mm/s'],
            'pressure' => ['value' => $this->faker->numberBetween(1, 100), 'unit' => 'PSI'],
            'humidity' => ['value' => $this->faker->numberBetween(20, 80), 'unit' => '%'],
            'power' => ['value' => $this->faker->numberBetween(100, 5000), 'unit' => 'W'],
            'flow' => ['value' => $this->faker->randomFloat(2, 0, 100), 'unit' => 'L/min'],
        ];

        $config = $metricConfig[$sensorType];

        return [
            'equipment_id' => Equipment::factory(),
            'sensor_type' => $sensorType,
            'metric_name' => $sensorType,
            'value' => $config['value'],
            'unit' => $config['unit'],
            'metadata' => [
                'battery_level' => $this->faker->numberBetween(50, 100) . '%',
                'signal_strength' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
            ],
            'status' => $this->faker->randomElement(['normal', 'warning', 'critical']),
            'reading_time' => $this->faker->dateTimeBetween('-7 days', 'now'),
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
