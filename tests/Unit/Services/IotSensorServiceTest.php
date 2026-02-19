<?php

namespace Tests\Unit\Services;

use App\Models\Equipment;
use App\Models\IotSensorReading;
use App\Services\IotSensorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IotSensorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected IotSensorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IotSensorService();
    }

    /** @test */
    public function it_stores_a_sensor_reading()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
            'sensor_id' => 'SENSOR-001',
        ]);

        $data = [
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'unit' => '°C',
            'reading_time' => now(),
        ];

        $reading = $this->service->storeReading($equipment, $data);

        $this->assertInstanceOf(IotSensorReading::class, $reading);
        $this->assertEquals(75.5, $reading->value);
        $this->assertEquals('temperature', $reading->metric_name);
    }

    /** @test */
    public function it_determines_normal_status_for_values_within_threshold()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
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

        $data = [
            'metric_name' => 'temperature',
            'value' => 50.0,
            'unit' => '°C',
        ];

        $reading = $this->service->storeReading($equipment, $data);

        $this->assertEquals('normal', $reading->status);
    }

    /** @test */
    public function it_determines_warning_status_for_values_exceeding_warning_threshold()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
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

        $data = [
            'metric_name' => 'temperature',
            'value' => 85.0,
            'unit' => '°C',
        ];

        $reading = $this->service->storeReading($equipment, $data);

        $this->assertEquals('warning', $reading->status);
    }

    /** @test */
    public function it_determines_critical_status_for_values_exceeding_critical_threshold()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
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

        $data = [
            'metric_name' => 'temperature',
            'value' => 95.0,
            'unit' => '°C',
        ];

        $reading = $this->service->storeReading($equipment, $data);

        $this->assertEquals('critical', $reading->status);
    }

    /** @test */
    public function it_updates_equipment_last_reading_timestamp()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
            'last_sensor_reading_at' => null,
        ]);

        $readingTime = now();
        
        $data = [
            'metric_name' => 'temperature',
            'value' => 75.5,
            'reading_time' => $readingTime,
        ];

        $this->service->storeReading($equipment, $data);
        $equipment->refresh();

        $this->assertNotNull($equipment->last_sensor_reading_at);
    }

    /** @test */
    public function it_generates_equipment_health_summary()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
        ]);

        // Create some readings
        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'unit' => '°C',
            'status' => 'normal',
            'reading_time' => now()->subMinutes(30),
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 76.0,
            'unit' => '°C',
            'status' => 'normal',
            'reading_time' => now()->subMinutes(15),
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 77.5,
            'unit' => '°C',
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $summary = $this->service->getEquipmentHealthSummary($equipment);

        $this->assertArrayHasKey('status', $summary);
        $this->assertArrayHasKey('metrics', $summary);
        $this->assertArrayHasKey('alerts_count', $summary);
        $this->assertEquals('healthy', $summary['status']);
        $this->assertEquals(0, $summary['alerts_count']);
        $this->assertCount(1, $summary['metrics']);
    }

    /** @test */
    public function it_counts_alerts_in_health_summary()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 95.0,
            'unit' => '°C',
            'status' => 'critical',
            'reading_time' => now(),
        ]);

        $summary = $this->service->getEquipmentHealthSummary($equipment);

        $this->assertEquals(1, $summary['alerts_count']);
        $this->assertEquals('critical', $summary['status']);
    }

    /** @test */
    public function it_returns_no_data_status_when_no_readings_exist()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
        ]);

        $summary = $this->service->getEquipmentHealthSummary($equipment);

        $this->assertEquals('no_data', $summary['status']);
        $this->assertEmpty($summary['metrics']);
    }

    /** @test */
    public function it_generates_predictive_insights()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
        ]);

        // Create readings showing an increasing trend
        for ($i = 0; $i < 15; $i++) {
            IotSensorReading::create([
                'equipment_id' => $equipment->id,
                'sensor_type' => 'temperature',
                'metric_name' => 'temperature',
                'value' => 70 + $i,
                'unit' => '°C',
                'status' => 'normal',
                'reading_time' => now()->subDays(15 - $i),
            ]);
        }

        $insights = $this->service->getPredictiveInsights($equipment);

        $this->assertArrayHasKey('temperature', $insights);
        $this->assertArrayHasKey('trend', $insights['temperature']);
        $this->assertEquals('increasing', $insights['temperature']['trend']);
    }

    /** @test */
    public function it_generates_real_time_dashboard_data()
    {
        // Create equipment with sensors
        Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
        ]);

        Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'vibration',
        ]);

        Equipment::factory()->create([
            'sensor_enabled' => false,
        ]);

        $dashboardData = $this->service->getRealTimeDashboardData();

        $this->assertArrayHasKey('total_monitored', $dashboardData);
        $this->assertArrayHasKey('healthy', $dashboardData);
        $this->assertArrayHasKey('warning', $dashboardData);
        $this->assertArrayHasKey('critical', $dashboardData);
        $this->assertArrayHasKey('critical_equipment', $dashboardData);
        $this->assertEquals(2, $dashboardData['total_monitored']);
    }
}
