<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Services\IotSensorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class IotSensorController extends Controller
{
    protected IotSensorService $sensorService;

    public function __construct(IotSensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    /**
     * Store a new sensor reading
     */
    public function storeReading(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sensor_id' => 'required|string|exists:equipment,sensor_id',
            'sensor_type' => 'sometimes|string',
            'metric_name' => 'required|string',
            'value' => 'required|numeric',
            'unit' => 'sometimes|string',
            'metadata' => 'sometimes|array',
            'reading_time' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $equipment = Equipment::where('sensor_id', $request->sensor_id)->first();

        if (!$equipment->sensor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor is not enabled for this equipment',
            ], 403);
        }

        $reading = $this->sensorService->storeReading($equipment, $request->all());

        return response()->json([
            'success' => true,
            'data' => $reading,
            'message' => 'Sensor reading stored successfully',
        ], 201);
    }

    /**
     * Store multiple sensor readings (batch)
     */
    public function storeBatchReadings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'readings' => 'required|array',
            'readings.*.sensor_id' => 'required|string|exists:equipment,sensor_id',
            'readings.*.sensor_type' => 'sometimes|string',
            'readings.*.metric_name' => 'required|string',
            'readings.*.value' => 'required|numeric',
            'readings.*.unit' => 'sometimes|string',
            'readings.*.metadata' => 'sometimes|array',
            'readings.*.reading_time' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $storedReadings = [];
        $errors = [];

        foreach ($request->readings as $index => $readingData) {
            try {
                $equipment = Equipment::where('sensor_id', $readingData['sensor_id'])->first();

                if (!$equipment->sensor_enabled) {
                    $errors[] = [
                        'index' => $index,
                        'sensor_id' => $readingData['sensor_id'],
                        'message' => 'Sensor is not enabled',
                    ];
                    continue;
                }

                $reading = $this->sensorService->storeReading($equipment, $readingData);
                $storedReadings[] = $reading;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'sensor_id' => $readingData['sensor_id'] ?? 'unknown',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'stored_count' => count($storedReadings),
            'errors_count' => count($errors),
            'errors' => $errors,
        ], 200);
    }

    /**
     * Get equipment health summary
     */
    public function getEquipmentHealth(Equipment $equipment): JsonResponse
    {
        $summary = $this->sensorService->getEquipmentHealthSummary($equipment);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get predictive insights for equipment
     */
    public function getPredictiveInsights(Equipment $equipment): JsonResponse
    {
        $insights = $this->sensorService->getPredictiveInsights($equipment);

        return response()->json([
            'success' => true,
            'data' => $insights,
        ]);
    }

    /**
     * Get real-time dashboard data
     */
    public function getDashboardData(): JsonResponse
    {
        $data = $this->sensorService->getRealTimeDashboardData();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
