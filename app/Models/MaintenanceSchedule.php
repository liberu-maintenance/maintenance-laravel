<?php

namespace App\Models;

use App\Notifications\TaskAssignedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'description',
    'equipment_id',
    'frequency_type',
    'frequency_value',
    'next_due_date',
    'last_completed_date',
    'estimated_duration',
    'priority',
    'status',
    'assigned_to',
    'instructions',
    'checklist_id',
    'team_id',
])]
class MaintenanceSchedule extends Model
{
    use HasFactory;

    /**
     * The relationships that should be eagerly loaded.
     *
     * @var array
     */
    #[\Override]
    protected $with = [];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function overdue($query)
    {
        return $query->where('next_due_date', '<', now())
                    ->where('status', 'active');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function dueSoon($query, $days = 7)
    {
        return $query->whereBetween('next_due_date', [now(), now()->addDays($days)])
                    ->where('status', 'active');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('status', 'active');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function inactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function calculateNextDueDate()
    {
        if (!$this->last_completed_date) {
            return $this->next_due_date;
        }

        $lastCompleted = $this->last_completed_date;

        switch ($this->frequency_type) {
            case 'daily':
                return $lastCompleted->addDays($this->frequency_value);
            case 'weekly':
                return $lastCompleted->addWeeks($this->frequency_value);
            case 'monthly':
                return $lastCompleted->addMonths($this->frequency_value);
            case 'yearly':
                return $lastCompleted->addYears($this->frequency_value);
            case 'hours':
                return $lastCompleted->addHours($this->frequency_value);
            default:
                return $this->next_due_date;
        }
    }

    public function markCompleted()
    {
        $this->update([
            'last_completed_date' => now(),
            'next_due_date' => $this->calculateNextDueDate(),
        ]);

        // Update equipment status if it was under maintenance
        if ($this->equipment && $this->equipment->status === 'under_maintenance') {
            // Check if there are any other active maintenance activities
            $hasActiveWorkOrders = $this->equipment->workOrders()
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->exists();
            
            // If no active work orders, set equipment back to active
            if (!$hasActiveWorkOrders) {
                $this->equipment->update(['status' => 'active']);
            }
        }

        // Send notification to assigned user about completion
        if ($this->assignedUser) {
            $this->assignedUser->notify(new TaskAssignedNotification($this, 'maintenance_schedule'));
        }
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Scope to get schedules with related data for listings
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withRelatedData($query)
    {
        return $query->with([
            'equipment:id,name,serial_number,status',
            'assignedUser:id,name',
            'checklist:id,name',
            'team:id,name',
        ]);
    }

    /**
     * Scope to get schedules with work order count
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withWorkOrderCount($query)
    {
        return $query->withCount([
            'workOrders',
            'workOrders as completed_work_orders_count' => function ($query) {
                $query->where('status', 'completed');
            }
        ]);
    }

    /**
     * Scope for upcoming maintenance (next 30 days)
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function upcoming($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('next_due_date', [now(), now()->addDays($days)])
            ->orderBy('next_due_date');
    }
    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
            'last_completed_date' => 'date',
            'estimated_duration' => 'integer',
        ];
    }
}