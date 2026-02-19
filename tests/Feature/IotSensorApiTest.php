<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\IotSensorReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IotSensorApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_a_sensor_reading()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
            'sensor_id' => 'SENSOR-001',
        ]);

        $response = $this->postJson('/api/iot-sensors/readings', [
            'sensor_id' => 'SENSOR-001',
            'sensor_type' => 'temperature',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'unit' => '°C',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Sensor reading stored successfully',
            ]);

        $this->assertDatabaseHas('iot_sensor_readings', [
            'equipment_id' => $equipment->id,
            'metric_name' => 'temperature',
            'value' => 75.5,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_sensor_reading()
    {
        $response = $this->postJson('/api/iot-sensors/readings', [
            'sensor_id' => 'SENSOR-001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['metric_name', 'value']);
    }

    /** @test */
    public function it_rejects_readings_for_non_existent_sensor()
    {
        $response = $this->postJson('/api/iot-sensors/readings', [
            'sensor_id' => 'NON-EXISTENT',
            'metric_name' => 'temperature',
            'value' => 75.5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sensor_id']);
    }

    /** @test */
    public function it_rejects_readings_for_disabled_sensor()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => false,
            'sensor_id' => 'SENSOR-001',
        ]);

        $response = $this->postJson('/api/iot-sensors/readings', [
            'sensor_id' => 'SENSOR-001',
            'metric_name' => 'temperature',
            'value' => 75.5,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Sensor is not enabled for this equipment',
            ]);
    }

    /** @test */
    public function it_can_store_batch_sensor_readings()
    {
        $equipment1 = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        $equipment2 = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-002',
        ]);

        $response = $this->postJson('/api/iot-sensors/readings/batch', [
            'readings' => [
                [
                    'sensor_id' => 'SENSOR-001',
                    'metric_name' => 'temperature',
                    'value' => 75.5,
                    'unit' => '°C',
                ],
                [
                    'sensor_id' => 'SENSOR-002',
                    'metric_name' => 'vibration',
                    'value' => 2.5,
                    'unit' => 'mm/s',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'stored_count' => 2,
                'errors_count' => 0,
            ]);

        $this->assertDatabaseHas('iot_sensor_readings', [
            'equipment_id' => $equipment1->id,
            'metric_name' => 'temperature',
        ]);

        $this->assertDatabaseHas('iot_sensor_readings', [
            'equipment_id' => $equipment2->id,
            'metric_name' => 'vibration',
        ]);
    }

    /** @test */
    public function it_handles_partial_failures_in_batch_readings()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        Equipment::factory()->create([
            'sensor_enabled' => false,
            'sensor_id' => 'SENSOR-002',
        ]);

        $response = $this->postJson('/api/iot-sensors/readings/batch', [
            'readings' => [
                [
                    'sensor_id' => 'SENSOR-001',
                    'metric_name' => 'temperature',
                    'value' => 75.5,
                ],
                [
                    'sensor_id' => 'SENSOR-002',
                    'metric_name' => 'temperature',
                    'value' => 80.0,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'stored_count' => 1,
                'errors_count' => 1,
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_equipment_health_endpoint()
    {
        $equipment = Equipment::factory()->create();

        $response = $this->getJson("/api/iot-sensors/equipment/{$equipment->id}/health");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_equipment_health_summary_for_authenticated_users()
    {
        $user = User::factory()->create();
        
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
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

        $response = $this->actingAs($user)
            ->getJson("/api/iot-sensors/equipment/{$equipment->id}/health");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'metrics',
                    'alerts_count',
                ],
            ]);
    }

    /** @test */
    public function it_returns_predictive_insights_for_authenticated_users()
    {
        $user = User::factory()->create();
        
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_type' => 'temperature',
        ]);

        // Create some readings for trend analysis
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

        $response = $this->actingAs($user)
            ->getJson("/api/iot-sensors/equipment/{$equipment->id}/insights");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function it_returns_dashboard_data_for_authenticated_users()
    {
        $user = User::factory()->create();
        
        Equipment::factory()->count(3)->create([
            'sensor_enabled' => true,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/iot-sensors/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_monitored',
                    'healthy',
                    'warning',
                    'critical',
                    'no_data',
                    'critical_equipment',
                ],
            ]);
    }

    /** @test */
    public function it_stores_metadata_with_sensor_reading()
    {
        $equipment = Equipment::factory()->create([
            'sensor_enabled' => true,
            'sensor_id' => 'SENSOR-001',
        ]);

        $response = $this->postJson('/api/iot-sensors/readings', [
            'sensor_id' => 'SENSOR-001',
            'metric_name' => 'temperature',
            'value' => 75.5,
            'unit' => '°C',
            'metadata' => [
                'battery_level' => '85%',
                'signal_strength' => 'good',
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('iot_sensor_readings', [
            'equipment_id' => $equipment->id,
            'metric_name' => 'temperature',
        ]);

        $reading = IotSensorReading::where('equipment_id', $equipment->id)->first();
        $this->assertArrayHasKey('battery_level', $reading->metadata);
    }
}
