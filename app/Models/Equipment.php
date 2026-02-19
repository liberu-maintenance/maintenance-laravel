<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'serial_number',
        'model',
        'manufacturer',
        'category',
        'location',
        'purchase_date',
        'warranty_expiry',
        'status',
        'criticality',
        'notes',
        'company_id',
        'team_id',
        'sensor_enabled',
        'sensor_type',
        'sensor_id',
        'sensor_config',
        'last_sensor_reading_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'sensor_enabled' => 'boolean',
        'sensor_config' => 'array',
        'last_sensor_reading_at' => 'datetime',
    ];

    /**
     * The relationships that should be eagerly loaded.
     *
     * @var array
     */
    protected $with = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all sensor readings for this equipment.
     */
    public function sensorReadings(): HasMany
    {
        return $this->hasMany(IotSensorReading::class);
    }

    /**
     * Get sensor readings from the last 24 hours for this equipment.
     */
    public function recentSensorReadings(): HasMany
    {
        return $this->hasMany(IotSensorReading::class)
            ->where('reading_time', '>=', now()->subHours(24))
            ->orderBy('reading_time', 'desc');
    }

    /**
     * Get critical status sensor readings for this equipment.
     */
    public function criticalSensorReadings(): HasMany
    {
        return $this->hasMany(IotSensorReading::class)
            ->where('status', 'critical')
            ->orderBy('reading_time', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'under_maintenance');
    }

    public function scopeCritical($query)
    {
        return $query->where('criticality', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('criticality', 'high');
    }

    public function scopeSensorEnabled($query)
    {
        return $query->where('sensor_enabled', true);
    }

    public function scopeWithCriticalReadings($query)
    {
        return $query->whereHas('sensorReadings', function ($q) {
            $q->where('status', 'critical')
              ->where('reading_time', '>=', now()->subHours(24));
        });
    }

    /**
     * Get the health status based on recent sensor readings
     */
    public function getHealthStatus(): string
    {
        if (!$this->sensor_enabled) {
            return 'unknown';
        }

        $criticalCount = $this->sensorReadings()
            ->where('status', 'critical')
            ->where('reading_time', '>=', now()->subHours(24))
            ->count();

        $warningCount = $this->sensorReadings()
            ->where('status', 'warning')
            ->where('reading_time', '>=', now()->subHours(24))
            ->count();

        if ($criticalCount > 0) {
            return 'critical';
        }

        if ($warningCount > 0) {
            return 'warning';
        }

        return 'healthy';
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    /**
     * Check if equipment has any active work orders
     */
    public function hasActiveWorkOrders(): bool
    {
        return $this->workOrders()
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->exists();
    }

    /**
     * Check if equipment can be set to active status
     */
    public function canBeSetToActive(): bool
    {
        return !$this->hasActiveWorkOrders();
    }

    /**
     * Automatically update equipment status based on work orders
     */
    public function syncStatusWithWorkOrders(): void
    {
        if ($this->hasActiveWorkOrders() && $this->status !== 'under_maintenance') {
            $this->update(['status' => 'under_maintenance']);
        } elseif (!$this->hasActiveWorkOrders() && $this->status === 'under_maintenance') {
            $this->update(['status' => 'active']);
        }
     * Scope to get equipment with work order counts
     */
    public function scopeWithWorkOrderCounts($query)
    {
        return $query->withCount([
            'workOrders',
            'workOrders as pending_work_orders_count' => function ($query) {
                $query->where('status', 'pending');
            },
            'workOrders as active_work_orders_count' => function ($query) {
                $query->whereIn('status', ['approved', 'in_progress']);
            }
        ]);
    }

    /**
     * Scope to get equipment with maintenance schedule counts
     */
    public function scopeWithMaintenanceCounts($query)
    {
        return $query->withCount([
            'maintenanceSchedules',
            'maintenanceSchedules as overdue_schedules_count' => function ($query) {
                $query->where('next_due_date', '<', now())
                     ->where('status', 'active');
            },
            'maintenanceSchedules as due_soon_schedules_count' => function ($query) {
                $query->whereBetween('next_due_date', [now(), now()->addDays(7)])
                     ->where('status', 'active');
            }
        ]);
    }
}