<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[\Illuminate\Database\Eloquent\Attributes\Fillable([
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
])]
class InventoryPart extends Model
{
    use HasFactory;

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
    protected function totalQuantity(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function () {
            return $this->stockLevels()->sum('quantity');
        });
    }

    /**
     * Get available quantity (total - reserved)
     */
    protected function availableQuantity(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function () {
            return $this->stockLevels()->sum('quantity') - $this->stockLevels()->sum('reserved_quantity');
        });
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
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function lowStock($query)
    {
        return $query->whereHas('stockLevels', function ($q) {
            $q->havingRaw('SUM(quantity) <= inventory_parts.reorder_level');
        });
    }

    /**
     * Scope for out of stock items
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function outOfStock($query)
    {
        return $query->whereHas('stockLevels', function ($q) {
            $q->havingRaw('SUM(quantity) = 0');
        });
    }
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'reorder_level' => 'integer',
            'reorder_quantity' => 'integer',
            'lead_time_days' => 'integer',
        ];
    }
}
