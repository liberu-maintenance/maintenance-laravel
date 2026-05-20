<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Team;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EquipmentTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $team;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->company = Company::factory()->create();
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function equipment_status_changes_to_under_maintenance_when_work_order_starts()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'active',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'pending',
            'team_id' => $this->team->id,
        ]);

        // Start the work order
        $workOrder->update(['status' => 'in_progress']);

        // Refresh equipment to get updated status
        $equipment->refresh();

        $this->assertEquals('under_maintenance', $equipment->status);
    }

    #[Test]
    public function equipment_status_changes_to_active_when_all_work_orders_complete()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'under_maintenance',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'in_progress',
            'team_id' => $this->team->id,
        ]);

        // Complete the work order
        $workOrder->update(['status' => 'completed']);

        // Refresh equipment to get updated status
        $equipment->refresh();

        $this->assertEquals('active', $equipment->status);
    }

    #[Test]
    public function equipment_remains_under_maintenance_when_other_work_orders_active()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'under_maintenance',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        $workOrder1 = WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'in_progress',
            'team_id' => $this->team->id,
        ]);

        $workOrder2 = WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'pending',
            'team_id' => $this->team->id,
        ]);

        // Complete first work order
        $workOrder1->update(['status' => 'completed']);

        // Refresh equipment to get updated status
        $equipment->refresh();

        // Equipment should still be under maintenance because workOrder2 is pending
        $this->assertEquals('under_maintenance', $equipment->status);
    }

    #[Test]
    public function equipment_status_updates_when_work_order_rejected()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'under_maintenance',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'in_progress',
            'team_id' => $this->team->id,
        ]);

        // Reject the work order
        $workOrder->update(['status' => 'rejected']);

        // Refresh equipment to get updated status
        $equipment->refresh();

        $this->assertEquals('active', $equipment->status);
    }

    #[Test]
    public function has_active_work_orders_returns_true_when_work_orders_active()
    {
        $equipment = Equipment::factory()->create([
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'in_progress',
            'team_id' => $this->team->id,
        ]);

        $this->assertTrue($equipment->hasActiveWorkOrders());
    }

    #[Test]
    public function has_active_work_orders_returns_false_when_no_active_work_orders()
    {
        $equipment = Equipment::factory()->create([
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'completed',
            'team_id' => $this->team->id,
        ]);

        $this->assertFalse($equipment->hasActiveWorkOrders());
    }

    #[Test]
    public function can_be_set_to_active_returns_false_when_work_orders_active()
    {
        $equipment = Equipment::factory()->create([
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'pending',
            'team_id' => $this->team->id,
        ]);

        $this->assertFalse($equipment->canBeSetToActive());
    }

    #[Test]
    public function sync_status_with_work_orders_updates_to_under_maintenance()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'active',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'pending',
            'team_id' => $this->team->id,
        ]);

        $equipment->syncStatusWithWorkOrders();
        $equipment->refresh();

        $this->assertEquals('under_maintenance', $equipment->status);
    }

    #[Test]
    public function sync_status_with_work_orders_updates_to_active()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'under_maintenance',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        // No active work orders

        $equipment->syncStatusWithWorkOrders();
        $equipment->refresh();

        $this->assertEquals('active', $equipment->status);
    }

    #[Test]
    public function maintenance_schedule_mark_completed_updates_equipment_status()
    {
        $equipment = Equipment::factory()->create([
            'status' => 'under_maintenance',
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
        ]);

        $schedule = MaintenanceSchedule::factory()->create([
            'equipment_id' => $equipment->id,
            'status' => 'active',
            'team_id' => $this->team->id,
        ]);

        // Mark schedule as completed
        $schedule->markCompleted();

        // Refresh equipment to get updated status
        $equipment->refresh();

        // Equipment should be active if no other work orders
        $this->assertEquals('active', $equipment->status);
    }

    #[Test]
    public function work_order_started_at_is_set_automatically()
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'pending',
            'team_id' => $this->team->id,
        ]);

        $this->assertNull($workOrder->started_at);

        $workOrder->update(['status' => 'in_progress']);
        $workOrder->refresh();

        $this->assertNotNull($workOrder->started_at);
    }

    #[Test]
    public function work_order_completed_at_is_set_automatically()
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'in_progress',
            'team_id' => $this->team->id,
        ]);

        $this->assertNull($workOrder->completed_at);

        $workOrder->update(['status' => 'completed']);
        $workOrder->refresh();

        $this->assertNotNull($workOrder->completed_at);
    }
}
