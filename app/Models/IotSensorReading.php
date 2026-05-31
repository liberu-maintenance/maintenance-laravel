<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'equipment_id',
    'sensor_type',
    'metric_name',
    'value',
    'unit',
    'metadata',
    'status',
    'reading_time',
])]
class IotSensorReading extends Model
{
    use HasFactory;

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Scope to get readings within a date range
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function betweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('reading_time', [$startDate, $endDate]);
    }

    /**
     * Scope to get readings by metric name
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function forMetric($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Scope to get critical readings
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function critical($query)
    {
        return $query->where('status', 'critical');
    }

    /**
     * Scope to get warning readings
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function warning($query)
    {
        return $query->where('status', 'warning');
    }

    /**
     * Scope to get recent readings
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function recent($query, $hours = 24)
    {
        return $query->where('reading_time', '>=', now()->subHours($hours));
    }

    /**
     * Determine if the reading is abnormal
     */
    public function isAbnormal(): bool
    {
        return in_array($this->status, ['warning', 'critical', 'error']);
    }
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'metadata' => 'array',
            'reading_time' => 'datetime',
        ];
    }
}
