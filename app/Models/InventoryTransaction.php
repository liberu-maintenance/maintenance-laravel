<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_part_id',
        'type',
        'quantity',
        'location',
        'work_order_id',
        'user_id',
        'notes',
        'unit_cost',
        'reference_number',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function inventoryPart(): BelongsTo
    {
        return $this->belongsTo(InventoryPart::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a stock-in transaction
     */
    public static function stockIn(
        int $inventoryPartId,
        int $quantity,
        string $location,
        ?int $userId = null,
        ?float $unitCost = null,
        ?string $notes = null,
        ?string $referenceNumber = null
    ): self {
        return self::create([
            'inventory_part_id' => $inventoryPartId,
            'type' => 'in',
            'quantity' => $quantity,
            'location' => $location,
            'user_id' => $userId,
            'unit_cost' => $unitCost,
            'notes' => $notes,
            'reference_number' => $referenceNumber,
        ]);
    }

    /**
     * Create a stock-out transaction
     */
    public static function stockOut(
        int $inventoryPartId,
        int $quantity,
        string $location,
        ?int $workOrderId = null,
        ?int $userId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'inventory_part_id' => $inventoryPartId,
            'type' => 'out',
            'quantity' => $quantity,
            'location' => $location,
            'work_order_id' => $workOrderId,
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }

    /**
     * Create a stock adjustment transaction
     */
    public static function stockAdjustment(
        int $inventoryPartId,
        int $quantity,
        string $location,
        ?int $userId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'inventory_part_id' => $inventoryPartId,
            'type' => 'adjustment',
            'quantity' => $quantity,
            'location' => $location,
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }
}
