<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_number',
        'name',
        'description',
        'category',
        'unit_of_measure',
        'unit_cost',
        'reorder_level',
        'reorder_quantity',
        'location',
        'supplier',
        'supplier_id',
        'lead_time_days',
        'notes',
        'team_id',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
        'lead_time_days' => 'integer',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function supplierCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'supplier_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(InventoryStockLevel::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_parts')
            ->withPivot(['quantity_planned', 'quantity_used', 'unit_cost', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get total quantity across all locations
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->stockLevels()->sum('quantity');
    }

    /**
     * Get available quantity (total - reserved)
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->stockLevels()->sum('quantity') - $this->stockLevels()->sum('reserved_quantity');
    }

    /**
     * Check if stock is below reorder level
     */
    public function isLowStock(): bool
    {
        return $this->total_quantity <= $this->reorder_level;
    }

    /**
     * Scope for low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('stockLevels', function ($q) {
            $q->havingRaw('SUM(quantity) <= inventory_parts.reorder_level');
        });
    }

    /**
     * Scope for out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereHas('stockLevels', function ($q) {
            $q->havingRaw('SUM(quantity) = 0');
        });
    }
}
