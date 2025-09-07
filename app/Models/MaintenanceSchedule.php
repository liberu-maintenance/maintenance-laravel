<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'last_completed_date' => 'date',
        'estimated_duration' => 'integer',
    ];

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

    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now())
                    ->where('status', 'active');
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereBetween('next_due_date', [now(), now()->addDays($days)])
                    ->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
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
    }
}