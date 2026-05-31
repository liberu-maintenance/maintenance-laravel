<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'name',
    'description',
    'category',
    'equipment_id',
    'is_template',
    'status',
    'created_by',
    'team_id',
])]
class Checklist extends Model
{
    use HasFactory;

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
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
    protected function templates($query)
    {
        return $query->where('is_template', true);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('status', 'active');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function duplicate($name = null)
    {
        $newChecklist = $this->replicate();
        $newChecklist->name = $name ?? $this->name . ' (Copy)';
        $newChecklist->is_template = false;
        $newChecklist->save();

        // Duplicate checklist items
        foreach ($this->items as $item) {
            $newItem = $item->replicate();
            $newItem->checklist_id = $newChecklist->id;
            $newItem->save();
        }

        return $newChecklist;
    }
    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
        ];
    }
}