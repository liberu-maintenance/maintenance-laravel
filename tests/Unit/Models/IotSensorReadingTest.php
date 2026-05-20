<?php

namespace Tests\Unit\Models;

use App\Models\Equipment;
use App\Models\IotSensorReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IotSensorReadingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'equipment_id',
            'sensor_type',
            'metric_name',
            'value',
            'unit',
            'metadata',
            'status',
            'reading_time',
        ];

        $reading = new IotSensorReading();

        $this->assertEquals($fillable, $reading->getFillable());
    }

    #[Test]
    public function it_belongs_to_equipment()
    {
        $equipment = Equipment::factory()->create();
        $reading = IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'unit' => '°C',
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $this->assertInstanceOf(Equipment::class, $reading->equipment);
        $this->assertEquals($equipment->id, $reading->equipment->id);
    }

    #[Test]
    public function it_can_filter_readings_by_date_range()
    {
        $equipment = Equipment::factory()->create();
        
        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'status' => 'normal',
            'reading_time' => now()->subDays(5),
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 80.0,
            'status' => 'normal',
            'reading_time' => now()->subDays(2),
        ]);

        $readings = IotSensorReading::betweenDates(
            now()->subDays(3),
            now()
        )->get();

        $this->assertCount(1, $readings);
    }

    #[Test]
    public function it_can_filter_readings_by_metric()
    {
        $equipment = Equipment::factory()->create();
        
        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'vibration',
            'metric_name' => 'vibration',
            'value' => 2.5,
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $tempReadings = IotSensorReading::forMetric('temperature')->get();

        $this->assertCount(1, $tempReadings);
        $this->assertEquals('temperature', $tempReadings->first()->metric_name);
    }

    #[Test]
    public function it_can_filter_critical_readings()
    {
        $equipment = Equipment::factory()->create();
        
        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 95.0,
            'status' => 'critical',
            'reading_time' => now(),
        ]);

        IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $criticalReadings = IotSensorReading::critical()->get();

        $this->assertCount(1, $criticalReadings);
        $this->assertEquals('critical', $criticalReadings->first()->status);
    }

    #[Test]
    public function it_can_detect_abnormal_readings()
    {
        $normalReading = IotSensorReading::create([
            'equipment_id' => Equipment::factory()->create()->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $criticalReading = IotSensorReading::create([
            'equipment_id' => Equipment::factory()->create()->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 95.0,
            'status' => 'critical',
            'reading_time' => now(),
        ]);

        $this->assertFalse($normalReading->isAbnormal());
        $this->assertTrue($criticalReading->isAbnormal());
    }

    #[Test]
    public function it_casts_metadata_to_array()
    {
        $reading = IotSensorReading::create([
            'equipment_id' => Equipment::factory()->create()->id,
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'metadata' => ['location' => 'sensor-1', 'battery' => '85%'],
            'status' => 'normal',
            'reading_time' => now(),
        ]);

        $this->assertIsArray($reading->metadata);
        $this->assertEquals('sensor-1', $reading->metadata['location']);
    }
}
