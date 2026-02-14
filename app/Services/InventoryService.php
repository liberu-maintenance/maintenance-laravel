<?php

namespace App\Services;

use App\Models\InventoryPart;
use App\Models\InventoryStockLevel;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to inventory
     */
    public function addStock(
        int $inventoryPartId,
        int $quantity,
        string $location,
        ?int $userId = null,
        ?float $unitCost = null,
        ?string $notes = null,
        ?string $referenceNumber = null
    ): InventoryTransaction {
        return DB::transaction(function () use (
            $inventoryPartId,
            $quantity,
            $location,
            $userId,
            $unitCost,
            $notes,
            $referenceNumber
        ) {
            // Create or update stock level
            $stockLevel = InventoryStockLevel::firstOrCreate(
                [
                    'inventory_part_id' => $inventoryPartId,
                    'location' => $location,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            $stockLevel->addStock($quantity);

            // Record transaction
            return InventoryTransaction::stockIn(
                $inventoryPartId,
                $quantity,
                $location,
                $userId,
                $unitCost,
                $notes,
                $referenceNumber
            );
        });
    }

    /**
     * Remove stock from inventory
     */
    public function removeStock(
        int $inventoryPartId,
        int $quantity,
        string $location,
        ?int $workOrderId = null,
        ?int $userId = null,
        ?string $notes = null
    ): InventoryTransaction {
        return DB::transaction(function () use (
            $inventoryPartId,
            $quantity,
            $location,
            $workOrderId,
            $userId,
            $notes
        ) {
            $stockLevel = InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
                ->where('location', $location)
                ->firstOrFail();

            $stockLevel->removeStock($quantity);

            // Record transaction
            return InventoryTransaction::stockOut(
                $inventoryPartId,
                $quantity,
                $location,
                $workOrderId,
                $userId,
                $notes
            );
        });
    }

    /**
     * Adjust stock levels (for inventory counts/corrections)
     */
    public function adjustStock(
        int $inventoryPartId,
        int $newQuantity,
        string $location,
        ?int $userId = null,
        ?string $notes = null
    ): InventoryTransaction {
        return DB::transaction(function () use (
            $inventoryPartId,
            $newQuantity,
            $location,
            $userId,
            $notes
        ) {
            $stockLevel = InventoryStockLevel::firstOrCreate(
                [
                    'inventory_part_id' => $inventoryPartId,
                    'location' => $location,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            $difference = $newQuantity - $stockLevel->quantity;
            $stockLevel->update(['quantity' => $newQuantity]);

            // Record transaction
            return InventoryTransaction::stockAdjustment(
                $inventoryPartId,
                $difference,
                $location,
                $userId,
                $notes
            );
        });
    }

    /**
     * Reserve stock for a work order
     */
    public function reserveStock(
        int $inventoryPartId,
        int $quantity,
        string $location
    ): void {
        $stockLevel = InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
            ->where('location', $location)
            ->firstOrFail();

        $stockLevel->reserveStock($quantity);
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(
        int $inventoryPartId,
        int $quantity,
        string $location
    ): void {
        $stockLevel = InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
            ->where('location', $location)
            ->first();

        if ($stockLevel) {
            $stockLevel->releaseReservedStock($quantity);
        }
    }

    /**
     * Get parts that are low on stock
     */
    public function getLowStockParts()
    {
        return InventoryPart::whereHas('stockLevels', function ($query) {
            $query->selectRaw('inventory_part_id, SUM(quantity) as total')
                ->groupBy('inventory_part_id')
                ->havingRaw('SUM(quantity) <= inventory_parts.reorder_level');
        })->get();
    }

    /**
     * Get parts that are out of stock
     */
    public function getOutOfStockParts()
    {
        return InventoryPart::whereDoesntHave('stockLevels', function ($query) {
            $query->where('quantity', '>', 0);
        })->get();
    }

    /**
     * Get stock level for a specific part and location
     */
    public function getStockLevel(int $inventoryPartId, string $location): ?InventoryStockLevel
    {
        return InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
            ->where('location', $location)
            ->first();
    }

    /**
     * Get total stock across all locations for a part
     */
    public function getTotalStock(int $inventoryPartId): int
    {
        return InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
            ->sum('quantity');
    }

    /**
     * Get available stock (total - reserved) for a part
     */
    public function getAvailableStock(int $inventoryPartId): int
    {
        $total = InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
            ->sum('quantity');
        $reserved = InventoryStockLevel::where('inventory_part_id', $inventoryPartId)
            ->sum('reserved_quantity');

        return $total - $reserved;
    }
}
