<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotSensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'sensor_type',
        'metric_name',
        'value',
        'unit',
        'metadata',
        'status',
        'reading_time',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'metadata' => 'array',
        'reading_time' => 'datetime',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Scope to get readings within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('reading_time', [$startDate, $endDate]);
    }

    /**
     * Scope to get readings by metric name
     */
    public function scopeForMetric($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Scope to get critical readings
     */
    public function scopeCritical($query)
    {
        return $query->where('status', 'critical');
    }

    /**
     * Scope to get warning readings
     */
    public function scopeWarning($query)
    {
        return $query->where('status', 'warning');
    }

    /**
     * Scope to get recent readings
     */
    public function scopeRecent($query, $hours = 24)
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
}
