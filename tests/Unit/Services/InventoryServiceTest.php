<?php

namespace Tests\Unit\Services;

use App\Models\InventoryPart;
use App\Models\InventoryStockLevel;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
    }

    /** @test */
    public function it_can_add_stock_to_inventory()
    {
        $part = InventoryPart::factory()->create();

        $transaction = $this->service->addStock(
            inventoryPartId: $part->id,
            quantity: 100,
            location: 'Warehouse A',
            userId: null,
            unitCost: 10.50,
            notes: 'Initial stock',
            referenceNumber: 'PO-001'
        );

        $this->assertInstanceOf(InventoryTransaction::class, $transaction);
        $this->assertEquals('in', $transaction->type);
        $this->assertEquals(100, $transaction->quantity);

        $stockLevel = InventoryStockLevel::where('inventory_part_id', $part->id)
            ->where('location', 'Warehouse A')
            ->first();

        $this->assertEquals(100, $stockLevel->quantity);
    }

    /** @test */
    public function it_can_remove_stock_from_inventory()
    {
        $part = InventoryPart::factory()->create();

        // Add stock first
        $this->service->addStock($part->id, 100, 'Warehouse A');

        // Remove stock
        $transaction = $this->service->removeStock(
            inventoryPartId: $part->id,
            quantity: 30,
            location: 'Warehouse A',
            notes: 'Used for work order'
        );

        $this->assertInstanceOf(InventoryTransaction::class, $transaction);
        $this->assertEquals('out', $transaction->type);
        $this->assertEquals(30, $transaction->quantity);

        $stockLevel = InventoryStockLevel::where('inventory_part_id', $part->id)
            ->where('location', 'Warehouse A')
            ->first();

        $this->assertEquals(70, $stockLevel->quantity);
    }

    /** @test */
    public function it_throws_exception_when_removing_more_stock_than_available()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $part = InventoryPart::factory()->create();
        $this->service->addStock($part->id, 10, 'Warehouse A');

        $this->service->removeStock($part->id, 20, 'Warehouse A');
    }

    /** @test */
    public function it_can_adjust_stock_levels()
    {
        $part = InventoryPart::factory()->create();

        // Set initial stock
        $this->service->addStock($part->id, 100, 'Warehouse A');

        // Adjust to 85
        $transaction = $this->service->adjustStock(
            inventoryPartId: $part->id,
            newQuantity: 85,
            location: 'Warehouse A',
            notes: 'Physical count adjustment'
        );

        $this->assertInstanceOf(InventoryTransaction::class, $transaction);
        $this->assertEquals('adjustment', $transaction->type);
        $this->assertEquals(-15, $transaction->quantity); // Difference

        $stockLevel = InventoryStockLevel::where('inventory_part_id', $part->id)
            ->where('location', 'Warehouse A')
            ->first();

        $this->assertEquals(85, $stockLevel->quantity);
    }

    /** @test */
    public function it_can_reserve_stock()
    {
        $part = InventoryPart::factory()->create();
        $this->service->addStock($part->id, 100, 'Warehouse A');

        $this->service->reserveStock($part->id, 25, 'Warehouse A');

        $stockLevel = InventoryStockLevel::where('inventory_part_id', $part->id)
            ->where('location', 'Warehouse A')
            ->first();

        $this->assertEquals(100, $stockLevel->quantity);
        $this->assertEquals(25, $stockLevel->reserved_quantity);
        $this->assertEquals(75, $stockLevel->available_quantity);
    }

    /** @test */
    public function it_can_release_reserved_stock()
    {
        $part = InventoryPart::factory()->create();
        $this->service->addStock($part->id, 100, 'Warehouse A');
        $this->service->reserveStock($part->id, 25, 'Warehouse A');

        $this->service->releaseReservedStock($part->id, 10, 'Warehouse A');

        $stockLevel = InventoryStockLevel::where('inventory_part_id', $part->id)
            ->where('location', 'Warehouse A')
            ->first();

        $this->assertEquals(15, $stockLevel->reserved_quantity);
        $this->assertEquals(85, $stockLevel->available_quantity);
    }

    /** @test */
    public function it_can_get_total_stock_for_a_part()
    {
        $part = InventoryPart::factory()->create();

        // Add stock in multiple locations
        $this->service->addStock($part->id, 50, 'Warehouse A');
        $this->service->addStock($part->id, 30, 'Warehouse B');
        $this->service->addStock($part->id, 20, 'Warehouse C');

        $total = $this->service->getTotalStock($part->id);

        $this->assertEquals(100, $total);
    }

    /** @test */
    public function it_can_get_available_stock_for_a_part()
    {
        $part = InventoryPart::factory()->create();

        $this->service->addStock($part->id, 100, 'Warehouse A');
        $this->service->reserveStock($part->id, 30, 'Warehouse A');

        $available = $this->service->getAvailableStock($part->id);

        $this->assertEquals(70, $available);
    }
}
