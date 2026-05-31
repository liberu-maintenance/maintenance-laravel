<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'title',
    'description',
    'priority',
    'status',
    'guest_name',
    'guest_email',
    'guest_phone',
    'location',
    'equipment_id',
    'maintenance_schedule_id',
    'checklist_id',
    'submitted_at',
    'reviewed_by',
    'reviewed_at',
    'notes',
    'team_id',
    'customer_id',
    'vendor_id',
    'assigned_to',
    'due_date',
    'started_at',
    'completed_at',
    'estimated_hours',
    'actual_hours',
])]
class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The relationships that should be eagerly loaded.
     *
     * @var array
     */
    #[\Override]
    protected $with = [];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function maintenanceSchedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'vendor_id', 'company_id');
    }

    public function vendorPerformanceEvaluations(): HasMany
    {
        return $this->hasMany(VendorPerformanceEvaluation::class);
    }

    public function inventoryParts(): BelongsToMany
    {
        return $this->belongsToMany(InventoryPart::class, 'work_order_parts')
            ->withPivot(['quantity_planned', 'quantity_used', 'unit_cost', 'notes'])
            ->withTimestamps();
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function pending($query)
    {
        return $query->where('status', 'pending');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function approved($query)
    {
        return $query->where('status', 'approved');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function rejected($query)
    {
        return $query->where('status', 'rejected');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function inProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function completed($query)
    {
        return $query->where('status', 'completed');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function overdue($query)
    {
        return $query->whereNotIn('status', ['completed', 'rejected'])
            ->where('due_date', '<', now());
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function assignedToUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function dueWithin($query, $days)
    {
        return $query->whereNotIn('status', ['completed', 'rejected'])
            ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WorkOrderComment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get work orders with related data for listings
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withRelatedData($query)
    {
        return $query->with([
            'equipment:id,name,serial_number,status',
            'customer:company_id,name',
            'assignedTo:id,name',
            'reviewer:id,name',
            'team:id,name',
        ]);
    }

    /**
     * Scope for efficient counting by status
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function countByStatus($query)
    {
        return $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status');
    }

    /**
     * Scope for efficient counting by priority
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function countByPriority($query)
    {
        return $query->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority');
    }
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'due_date' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_hours' => 'integer',
            'actual_hours' => 'integer',
        ];
    }
}