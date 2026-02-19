<?php

namespace Tests\Unit\Models;

use App\Models\Equipment;
use App\Models\IotSensorReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentIotSensorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_iot_sensor_fillable_attributes()
    {
        $equipment = new Equipment();
        $fillable = $equipment->getFillable();

        $this->assertContains('sensor_enabled', $fillable);
        $this->assertContains('sensor_type', $fillable);
        $this->assertContains('sensor_id', $fillable);
        $this->assertContains('sensor_config', $fillable);
        $this->assertContains('last_sensor_reading_at', $fillable);
    }

    /** @test */
    public function it_has_sensor_readings_relationship()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
            'sensor_id' => 'SENSOR-001',
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'unit' => '°C',
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $this->assertCount(1, $equipment->sensorReadings);
    }

    /** @test */
    public function it_can_filter_sensor_enabled_equipment()
    {
        Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        Equipment::factory()->create([
            'sensor_enabled' => false,
        ]);

        $sensorEnabled = Equipment::sensorEnabled()->get();

        $this->assertCount(1, $sensorEnabled);
        $this->assertTrue($sensorEnabled->first()->sensor_enabled);
    }

    /** @test */
    public function it_can_filter_equipment_with_critical_readings()
    {
        $equipment1 = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        $equipment2 = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-002',
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment1->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 95.0,
            'status' => 'critical',
            'reading_time' => now(),
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment2->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $criticalEquipment = Equipment::withCriticalReadings()->get();

        $this->assertCount(1, $criticalEquipment);
        $this->assertEquals($equipment1->id, $criticalEquipment->first()->id);
    }

    /** @test */
    public function it_returns_healthy_status_for_equipment_with_normal_readings()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $this->assertEquals('healthy', $equipment->getHealthStatus());
    }

    /** @test */
    public function it_returns_warning_status_for_equipment_with_warning_readings()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 85.0,
            'status' => 'warning',
            'reading_time' => now(),
        ]);

        $this->assertEquals('warning', $equipment->getHealthStatus());
    }

    /** @test */
    public function it_returns_critical_status_for_equipment_with_critical_readings()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 95.0,
            'status' => 'critical',
            'reading_time' => now(),
        ]);

        $this->assertEquals('critical', $equipment->getHealthStatus());
    }

    /** @test */
    public function it_returns_unknown_status_for_equipment_without_sensors()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => false,
        ]);

        $this->assertEquals('unknown', $equipment->getHealthStatus());
    }

    /** @test */
    public function it_casts_sensor_config_to_array()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
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

        $this->assertIsArray($equipment->sensor_config);
        $this->assertArrayHasKey('thresholds', $equipment->sensor_config);
    }

    /** @test */
    public function it_updates_last_sensor_reading_timestamp()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
            'last_sensor_reading_at' => null,
        ]);

        $readingTime = now();
        
        $equipment->update([
            'last_sensor_reading_at' => $readingTime,
        ]);

        $equipment->refresh();

        $this->assertNotNull($equipment->last_sensor_reading_at);
        $this->assertEquals(
            $readingTime->format('Y-m-d H:i:s'),
            $equipment->last_sensor_reading_at->format('Y-m-d H:i:s')
        );
    }
}
