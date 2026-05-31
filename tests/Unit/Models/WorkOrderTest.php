<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Checklist;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\InventoryPart;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_expected_fillable_attributes(): void
    {
        $workOrder = new WorkOrder();
        $fillable  = $workOrder->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('priority', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('team_id', $fillable);
        $this->assertContains('equipment_id', $fillable);
    }

    #[Test]
    public function it_belongs_to_a_team(): void
    {
        $team      = Team::factory()->create();
        $workOrder = WorkOrder::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $workOrder->team);
        $this->assertEquals($team->id, $workOrder->team->id);
    }

    #[Test]
    public function it_belongs_to_equipment(): void
    {
        $equipment = Equipment::factory()->create();
        $workOrder = WorkOrder::factory()->create(['equipment_id' => $equipment->id]);

        $this->assertInstanceOf(Equipment::class, $workOrder->equipment);
        $this->assertEquals($equipment->id, $workOrder->equipment->id);
    }

    #[Test]
    public function it_belongs_to_a_checklist(): void
    {
        $checklist = Checklist::factory()->create();
        $workOrder = WorkOrder::factory()->create(['checklist_id' => $checklist->id]);

        $this->assertInstanceOf(Checklist::class, $workOrder->checklist);
    }

    #[Test]
    public function it_belongs_to_a_maintenance_schedule(): void
    {
        $schedule  = MaintenanceSchedule::factory()->create();
        $workOrder = WorkOrder::factory()->create(['maintenance_schedule_id' => $schedule->id]);

        $this->assertInstanceOf(MaintenanceSchedule::class, $workOrder->maintenanceSchedule);
    }

    #[Test]
    public function it_belongs_to_an_assigned_user(): void
    {
        $user      = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['assigned_to' => $user->id]);

        $this->assertInstanceOf(User::class, $workOrder->assignedTo);
        $this->assertEquals($user->id, $workOrder->assignedTo->id);
    }

    #[Test]
    public function pending_scope_filters_by_pending_status(): void
    {
        WorkOrder::factory()->count(2)->create(['status' => 'pending']);
        WorkOrder::factory()->count(3)->create(['status' => 'approved']);

        $pending = WorkOrder::pending()->get();

        $this->assertCount(2, $pending);
        $pending->each(fn($wo) => $this->assertEquals('pending', $wo->status));
    }

    #[Test]
    public function completed_scope_filters_by_completed_status(): void
    {
        WorkOrder::factory()->count(3)->create(['status' => 'completed']);
        WorkOrder::factory()->count(2)->create(['status' => 'pending']);

        $completed = WorkOrder::completed()->get();

        $this->assertCount(3, $completed);
    }

    #[Test]
    public function in_progress_scope_filters_correctly(): void
    {
        WorkOrder::factory()->count(2)->create(['status' => 'in_progress']);
        WorkOrder::factory()->create(['status' => 'pending']);

        $inProgress = WorkOrder::inProgress()->get();

        $this->assertCount(2, $inProgress);
    }

    #[Test]
    public function overdue_scope_returns_past_due_incomplete_orders(): void
    {
        // Overdue: past due_date, not completed
        WorkOrder::factory()->create([
            'status'   => 'in_progress',
            'due_date' => now()->subDays(2),
        ]);

        // Not overdue: past due_date but completed
        WorkOrder::factory()->create([
            'status'   => 'completed',
            'due_date' => now()->subDays(2),
        ]);

        // Not overdue: future due_date
        WorkOrder::factory()->create([
            'status'   => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $overdue = WorkOrder::overdue()->get();

        $this->assertCount(1, $overdue);
    }

    #[Test]
    public function it_can_have_inventory_parts(): void
    {
        $part      = InventoryPart::factory()->create();
        $workOrder = WorkOrder::factory()->create();

        $workOrder->inventoryParts()->attach($part->id, [
            'quantity_planned' => 3,
            'quantity_used'    => 2,
            'unit_cost'        => 25.00,
        ]);

        $this->assertCount(1, $workOrder->inventoryParts);
        $this->assertEquals(3, $workOrder->inventoryParts->first()->pivot->quantity_planned);
    }

    #[Test]
    public function soft_deleted_work_order_is_not_in_default_query(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $id        = $workOrder->id;

        $workOrder->delete();

        $this->assertNull(WorkOrder::find($id));
        $this->assertNotNull(WorkOrder::withTrashed()->find($id));
    }
}
