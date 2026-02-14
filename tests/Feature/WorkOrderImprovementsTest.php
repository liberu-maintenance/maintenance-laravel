<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user and team
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->current_team_id = $this->team->id;
        $this->user->save();
    }

    /** @test */
    public function work_order_can_have_assigned_user(): void
    {
        $assignee = User::factory()->create();
        
        $workOrder = WorkOrder::factory()->create([
            'assigned_to' => $assignee->id,
            'team_id' => $this->team->id,
        ]);

        $this->assertNotNull($workOrder->assigned_to);
        $this->assertEquals($assignee->id, $workOrder->assigned_to);
        $this->assertInstanceOf(User::class, $workOrder->assignedTo);
    }

    /** @test */
    public function work_order_can_have_due_date(): void
    {
        $dueDate = now()->addDays(7);
        
        $workOrder = WorkOrder::factory()->create([
            'due_date' => $dueDate,
            'team_id' => $this->team->id,
        ]);

        $this->assertNotNull($workOrder->due_date);
        $this->assertEquals($dueDate->format('Y-m-d H:i'), $workOrder->due_date->format('Y-m-d H:i'));
    }

    /** @test */
    public function work_order_tracks_started_and_completed_timestamps(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'approved',
            'team_id' => $this->team->id,
        ]);

        // Start the work order
        $workOrder->update(['status' => 'in_progress']);
        $this->assertNotNull($workOrder->fresh()->started_at);

        // Complete the work order
        $workOrder->update(['status' => 'completed']);
        $this->assertNotNull($workOrder->fresh()->completed_at);
    }

    /** @test */
    public function work_order_can_track_estimated_and_actual_hours(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'estimated_hours' => 8,
            'actual_hours' => 10,
            'team_id' => $this->team->id,
        ]);

        $this->assertEquals(8, $workOrder->estimated_hours);
        $this->assertEquals(10, $workOrder->actual_hours);
    }

    /** @test */
    public function work_order_overdue_scope_returns_overdue_orders(): void
    {
        // Create overdue work order
        $overdueOrder = WorkOrder::factory()->create([
            'status' => 'in_progress',
            'due_date' => now()->subDays(2),
            'team_id' => $this->team->id,
        ]);

        // Create future work order
        $futureOrder = WorkOrder::factory()->create([
            'status' => 'in_progress',
            'due_date' => now()->addDays(2),
            'team_id' => $this->team->id,
        ]);

        // Create completed work order (should not be overdue)
        $completedOrder = WorkOrder::factory()->create([
            'status' => 'completed',
            'due_date' => now()->subDays(2),
            'team_id' => $this->team->id,
        ]);

        $overdueOrders = WorkOrder::overdue()->get();

        $this->assertTrue($overdueOrders->contains($overdueOrder));
        $this->assertFalse($overdueOrders->contains($futureOrder));
        $this->assertFalse($overdueOrders->contains($completedOrder));
    }

    /** @test */
    public function work_order_assigned_to_scope_filters_by_user(): void
    {
        $assignee1 = User::factory()->create();
        $assignee2 = User::factory()->create();

        $order1 = WorkOrder::factory()->create([
            'assigned_to' => $assignee1->id,
            'team_id' => $this->team->id,
        ]);

        $order2 = WorkOrder::factory()->create([
            'assigned_to' => $assignee2->id,
            'team_id' => $this->team->id,
        ]);

        $assignedOrders = WorkOrder::assignedTo($assignee1->id)->get();

        $this->assertTrue($assignedOrders->contains($order1));
        $this->assertFalse($assignedOrders->contains($order2));
    }

    /** @test */
    public function work_order_due_within_scope_returns_orders_due_soon(): void
    {
        // Order due in 3 days
        $dueSoon = WorkOrder::factory()->create([
            'status' => 'in_progress',
            'due_date' => now()->addDays(3),
            'team_id' => $this->team->id,
        ]);

        // Order due in 10 days
        $dueLater = WorkOrder::factory()->create([
            'status' => 'in_progress',
            'due_date' => now()->addDays(10),
            'team_id' => $this->team->id,
        ]);

        $ordersDueWithinWeek = WorkOrder::dueWithin(7)->get();

        $this->assertTrue($ordersDueWithinWeek->contains($dueSoon));
        $this->assertFalse($ordersDueWithinWeek->contains($dueLater));
    }

    /** @test */
    public function work_order_supports_soft_deletes(): void
    {
        $workOrder = WorkOrder::factory()->create(['team_id' => $this->team->id]);
        $workOrderId = $workOrder->id;

        $workOrder->delete();

        // Should not be in normal queries
        $this->assertNull(WorkOrder::find($workOrderId));

        // Should be in trashed queries
        $this->assertNotNull(WorkOrder::withTrashed()->find($workOrderId));

        // Can be restored
        $workOrder->restore();
        $this->assertNotNull(WorkOrder::find($workOrderId));
    }

    /** @test */
    public function observer_automatically_sets_submitted_at_on_create(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'submitted_at' => null,
        ]);

        // Observer should set submitted_at
        $this->assertNotNull($workOrder->fresh()->submitted_at);
    }
}
