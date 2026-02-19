<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\IotSensorReading;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IotSensorService
{
    /**
     * Process and store a new sensor reading
     */
    public function storeReading(Equipment $equipment, array $data): IotSensorReading
    {
        $status = $this->determineReadingStatus(
            $data['metric_name'],
            $data['value'],
            $equipment->sensor_config ?? []
        );

        $reading = IotSensorReading::create([
            'equipment_id' => $equipment->id,
            'sensor_type' => $data['sensor_type'] ?? $equipment->sensor_type,
            'metric_name' => $data['metric_name'],
            'value' => $data['value'],
            'unit' => $data['unit'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'status' => $status,
            'reading_time' => $data['reading_time'] ?? now(),
        ]);

        // Update equipment's last reading timestamp
        $equipment->update(['last_sensor_reading_at' => $reading->reading_time]);

        // Check if maintenance is needed based on the reading
        $this->checkMaintenanceRequirement($equipment, $reading);

        return $reading;
    }

    /**
     * Determine the status of a sensor reading based on thresholds
     */
    protected function determineReadingStatus(string $metricName, float $value, array $config): string
    {
        $thresholds = $config['thresholds'][$metricName] ?? null;

        if (!$thresholds) {
            return 'normal';
        }

        if (isset($thresholds['critical_min']) && $value < $thresholds['critical_min']) {
            return 'critical';
        }

        if (isset($thresholds['critical_max']) && $value > $thresholds['critical_max']) {
            return 'critical';
        }

        if (isset($thresholds['warning_min']) && $value < $thresholds['warning_min']) {
            return 'warning';
        }

        if (isset($thresholds['warning_max']) && $value > $thresholds['warning_max']) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Check if maintenance is required based on sensor reading
     */
    protected function checkMaintenanceRequirement(Equipment $equipment, IotSensorReading $reading): void
    {
        if ($reading->status === 'critical') {
            // Could trigger a work order creation or notification here
            // This is a placeholder for future enhancement
        }
    }

    /**
     * Get equipment health summary based on sensor data
     */
    public function getEquipmentHealthSummary(Equipment $equipment, int $hours = 24): array
    {
        $readings = $equipment->sensorReadings()
            ->where('reading_time', '>=', now()->subHours($hours))
            ->orderBy('reading_time', 'desc')
            ->get();

        if ($readings->isEmpty()) {
            return [
                'status' => 'no_data',
                'metrics' => [],
                'alerts_count' => 0,
            ];
        }

        $metrics = $readings->groupBy('metric_name')->map(function ($metricReadings, $metricName) {
            $values = $metricReadings->pluck('value');
            return [
                'name' => $metricName,
                'current' => $metricReadings->first()->value,
                'average' => round($values->avg(), 2),
                'min' => $values->min(),
                'max' => $values->max(),
                'unit' => $metricReadings->first()->unit,
                'readings_count' => $metricReadings->count(),
                'status' => $metricReadings->first()->status,
            ];
        })->values();

        $alertsCount = $readings->filter(fn($r) => $r->isAbnormal())->count();

        return [
            'status' => $equipment->getHealthStatus(),
            'metrics' => $metrics->toArray(),
            'alerts_count' => $alertsCount,
            'last_reading_at' => $readings->first()->reading_time,
        ];
    }

    /**
     * Get predictive maintenance insights
     */
    public function getPredictiveInsights(Equipment $equipment, int $days = 30): array
    {
        $readings = $equipment->sensorReadings()
            ->where('reading_time', '>=', now()->subDays($days))
            ->orderBy('reading_time', 'asc')
            ->get();

        if ($readings->count() < 10) {
            return [
                'trend' => 'insufficient_data',
                'prediction' => null,
                'confidence' => 0,
            ];
        }

        $insights = [];

        // Analyze trends for each metric
        $metricGroups = $readings->groupBy('metric_name');

        foreach ($metricGroups as $metricName => $metricReadings) {
            $trend = $this->analyzeTrend($metricReadings);
            $insights[$metricName] = $trend;
        }

        return $insights;
    }

    /**
     * Analyze trend for a metric
     */
    protected function analyzeTrend(Collection $readings): array
    {
        $values = $readings->pluck('value')->toArray();
        $count = count($values);

        if ($count < 2) {
            return [
                'trend' => 'stable',
                'direction' => 'none',
                'rate_of_change' => 0,
            ];
        }

        // Simple linear regression to detect trend
        $x = range(0, $count - 1);
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $count; $i++) {
            $sumXY += $x[$i] * $values[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $slope = ($count * $sumXY - $sumX * $sumY) / ($count * $sumX2 - $sumX * $sumX);

        $direction = 'stable';
        if (abs($slope) > 0.1) {
            $direction = $slope > 0 ? 'increasing' : 'decreasing';
        }

        return [
            'trend' => $direction !== 'stable' ? $direction : 'stable',
            'direction' => $direction,
            'rate_of_change' => round($slope, 4),
        ];
    }

    /**
     * Get real-time dashboard data
     */
    public function getRealTimeDashboardData(): array
    {
        $sensorEnabledEquipment = Equipment::sensorEnabled()
            ->with(['latestSensorReadings' => function ($query) {
                $query->limit(1);
            }])
            ->get();

        $healthyCount = 0;
        $warningCount = 0;
        $criticalCount = 0;
        $noDataCount = 0;

        foreach ($sensorEnabledEquipment as $equipment) {
            $status = $equipment->getHealthStatus();
            match ($status) {
                'healthy' => $healthyCount++,
                'warning' => $warningCount++,
                'critical' => $criticalCount++,
                default => $noDataCount++,
            };
        }

        return [
            'total_monitored' => $sensorEnabledEquipment->count(),
            'healthy' => $healthyCount,
            'warning' => $warningCount,
            'critical' => $criticalCount,
            'no_data' => $noDataCount,
            'critical_equipment' => $sensorEnabledEquipment
                ->filter(fn($e) => $e->getHealthStatus() === 'critical')
                ->map(fn($e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'location' => $e->location,
                    'last_reading' => $e->last_sensor_reading_at,
                ])
                ->values()
                ->toArray(),
        ];
    }
}
