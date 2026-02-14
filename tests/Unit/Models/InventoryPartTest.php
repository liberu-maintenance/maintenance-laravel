<?php

namespace Tests\Unit\Models;

use App\Models\InventoryPart;
use App\Models\InventoryStockLevel;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
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
            'lead_time_days',
            'notes',
            'team_id',
        ];

        $part = new InventoryPart();

        $this->assertEquals($fillable, $part->getFillable());
    }

    /** @test */
    public function it_can_have_stock_levels()
    {
        $part = InventoryPart::factory()->create();

        InventoryStockLevel::create([
            'inventory_part_id' => $part->id,
            'location' => 'Warehouse A',
            'quantity' => 100,
            'reserved_quantity' => 10,
        ]);

        $this->assertCount(1, $part->stockLevels);
        $this->assertEquals(100, $part->stockLevels->first()->quantity);
    }

    /** @test */
    public function it_calculates_total_quantity_across_locations()
    {
        $part = InventoryPart::factory()->create();

        InventoryStockLevel::create([
            'inventory_part_id' => $part->id,
            'location' => 'Warehouse A',
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        InventoryStockLevel::create([
            'inventory_part_id' => $part->id,
            'location' => 'Warehouse B',
            'quantity' => 30,
            'reserved_quantity' => 0,
        ]);

        $part->refresh();

        $this->assertEquals(80, $part->total_quantity);
    }

    /** @test */
    public function it_calculates_available_quantity()
    {
        $part = InventoryPart::factory()->create();

        InventoryStockLevel::create([
            'inventory_part_id' => $part->id,
            'location' => 'Warehouse A',
            'quantity' => 100,
            'reserved_quantity' => 25,
        ]);

        $part->refresh();

        $this->assertEquals(75, $part->available_quantity);
    }

    /** @test */
    public function it_can_detect_low_stock()
    {
        $part = InventoryPart::factory()->create([
            'reorder_level' => 50,
        ]);

        InventoryStockLevel::create([
            'inventory_part_id' => $part->id,
            'location' => 'Warehouse A',
            'quantity' => 30,
            'reserved_quantity' => 0,
        ]);

        $part->refresh();

        $this->assertTrue($part->isLowStock());
    }

    /** @test */
    public function it_can_detect_adequate_stock()
    {
        $part = InventoryPart::factory()->create([
            'reorder_level' => 50,
        ]);

        InventoryStockLevel::create([
            'inventory_part_id' => $part->id,
            'location' => 'Warehouse A',
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $part->refresh();

        $this->assertFalse($part->isLowStock());
    }

    /** @test */
    public function it_can_be_associated_with_work_orders()
    {
        $part = InventoryPart::factory()->create();
        $workOrder = WorkOrder::factory()->create();

        $workOrder->inventoryParts()->attach($part->id, [
            'quantity_planned' => 5,
            'quantity_used' => 3,
            'unit_cost' => 12.50,
        ]);

        $this->assertCount(1, $part->workOrders);
        $this->assertEquals(5, $part->workOrders->first()->pivot->quantity_planned);
    }
}
