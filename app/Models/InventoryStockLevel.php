<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_part_id',
        'location',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function inventoryPart(): BelongsTo
    {
        return $this->belongsTo(InventoryPart::class);
    }

    /**
     * Get available quantity (quantity - reserved)
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Add stock to this location
     */
    public function addStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove stock from this location
     */
    public function removeStock(int $quantity): void
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$this->available_quantity}, Requested: {$quantity}");
        }
        $this->decrement('quantity', $quantity);
    }

    /**
     * Reserve stock for a work order
     */
    public function reserveStock(int $quantity): void
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception("Insufficient stock to reserve. Available: {$this->available_quantity}, Requested: {$quantity}");
        }
        $this->increment('reserved_quantity', $quantity);
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(int $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }
}
