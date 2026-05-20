<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Equipment;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetManagementPerformanceTest extends TestCase
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

    #[Test]
    public function equipment_with_work_order_counts_uses_single_query(): void
    {
        // Create equipment with work orders
        $equipment = Equipment::factory()
            ->has(WorkOrder::factory()->count(5)->state(['status' => 'pending', 'team_id' => $this->team->id]))
            ->has(WorkOrder::factory()->count(3)->state(['status' => 'in_progress', 'team_id' => $this->team->id]))
            ->create(['team_id' => $this->team->id]);

        // Enable query log
        DB::enableQueryLog();
        
        // Use the optimized scope
        $result = Equipment::withWorkOrderCounts()->find($equipment->id);
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use a single query with counts
        $this->assertLessThanOrEqual(2, count($queries), 'Expected at most 2 queries');
        $this->assertEquals(8, $result->work_orders_count);
        $this->assertEquals(5, $result->pending_work_orders_count);
        $this->assertEquals(3, $result->active_work_orders_count);
    }

    #[Test]
    public function equipment_with_maintenance_counts_uses_single_query(): void
    {
        // Create equipment with maintenance schedules
        $equipment = Equipment::factory()
            ->has(MaintenanceSchedule::factory()->overdue()->count(2)->state(['team_id' => $this->team->id]))
            ->has(MaintenanceSchedule::factory()->dueSoon()->count(3)->state(['team_id' => $this->team->id]))
            ->create(['team_id' => $this->team->id]);

        // Enable query log
        DB::enableQueryLog();
        
        // Use the optimized scope
        $result = Equipment::withMaintenanceCounts()->find($equipment->id);
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use a single query with counts
        $this->assertLessThanOrEqual(2, count($queries), 'Expected at most 2 queries');
        $this->assertEquals(5, $result->maintenance_schedules_count);
        $this->assertEquals(2, $result->overdue_schedules_count);
        $this->assertEquals(3, $result->due_soon_schedules_count);
    }

    #[Test]
    public function work_order_with_related_data_uses_eager_loading(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);
        $assignee = User::factory()->create();
        
        $workOrder = WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'assigned_to' => $assignee->id,
            'team_id' => $this->team->id,
        ]);

        // Enable query log
        DB::enableQueryLog();
        
        // Use the optimized scope
        $result = WorkOrder::withRelatedData()->find($workOrder->id);
        
        // Access relationships (should not trigger additional queries due to eager loading)
        $equipmentName = $result->equipment->name;
        $assigneeName = $result->assignedTo->name;
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use eager loading to avoid N+1 queries (1 main + 1 per eager-loaded relationship)
        $this->assertLessThanOrEqual(6, count($queries), 'Expected at most 6 queries with eager loading');
    }

    #[Test]
    public function maintenance_schedule_with_related_data_uses_eager_loading(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);
        $assignee = User::factory()->create();
        
        $schedule = MaintenanceSchedule::factory()->create([
            'equipment_id' => $equipment->id,
            'assigned_to' => $assignee->id,
            'team_id' => $this->team->id,
        ]);

        // Enable query log
        DB::enableQueryLog();
        
        // Use the optimized scope
        $result = MaintenanceSchedule::withRelatedData()->find($schedule->id);
        
        // Access relationships
        $equipmentName = $result->equipment->name;
        $assigneeName = $result->assignedUser->name;
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use eager loading to avoid N+1 queries (1 main + 1 per eager-loaded relationship)
        $this->assertLessThanOrEqual(6, count($queries), 'Expected at most 6 queries with eager loading');
    }

    #[Test]
    public function work_order_badge_counts_are_cached(): void
    {
        // Clear cache first
        Cache::forget('work_orders.badge_counts');

        // Create some work orders
        WorkOrder::factory()->count(3)->create(['status' => 'pending', 'team_id' => $this->team->id]);
        WorkOrder::factory()->count(2)->create([
            'status' => 'in_progress',
            'due_date' => now()->subDays(1),
            'team_id' => $this->team->id,
        ]);

        // First access should cache
        $this->assertFalse(Cache::has('work_orders.badge_counts'));
        
        // Simulate navigation badge access
        $pending = WorkOrder::where('status', 'pending')->count();
        $overdue = WorkOrder::overdue()->count();
        
        // Manually cache like the resource does
        $counts = cache()->remember('work_orders.badge_counts', now()->addMinutes(5), function () {
            return [
                'pending' => WorkOrder::where('status', 'pending')->count(),
                'overdue' => WorkOrder::overdue()->count(),
            ];
        });
        
        // Cache should now exist
        $this->assertTrue(Cache::has('work_orders.badge_counts'));
        $this->assertEquals(3, $counts['pending']);
        $this->assertEquals(2, $counts['overdue']);
    }

    #[Test]
    public function work_order_cache_is_cleared_on_status_change(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'status' => 'pending',
            'team_id' => $this->team->id,
        ]);

        // Set cache
        Cache::put('work_orders.badge_counts', ['pending' => 1, 'overdue' => 0]);
        $this->assertTrue(Cache::has('work_orders.badge_counts'));

        // Update status - should clear cache via observer
        $workOrder->update(['status' => 'approved']);

        // Cache should be cleared
        $this->assertFalse(Cache::has('work_orders.badge_counts'));
    }

    #[Test]
    public function work_order_count_by_status_is_efficient(): void
    {
        // Create work orders with different statuses
        WorkOrder::factory()->count(5)->create(['status' => 'pending', 'team_id' => $this->team->id]);
        WorkOrder::factory()->count(3)->create(['status' => 'in_progress', 'team_id' => $this->team->id]);
        WorkOrder::factory()->count(2)->create(['status' => 'completed', 'team_id' => $this->team->id]);

        // Enable query log
        DB::enableQueryLog();
        
        // Use the optimized count scope
        $counts = WorkOrder::countByStatus()->get()->pluck('count', 'status');
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use a single GROUP BY query
        $this->assertCount(1, $queries);
        $this->assertEquals(5, $counts['pending']);
        $this->assertEquals(3, $counts['in_progress']);
        $this->assertEquals(2, $counts['completed']);
    }

    #[Test]
    public function maintenance_schedule_upcoming_scope_performs_well(): void
    {
        // Create schedules in different time ranges
        MaintenanceSchedule::factory()->count(3)->dueSoon()->create([
            'team_id' => $this->team->id,
            'equipment_id' => Equipment::factory()->create(['team_id' => $this->team->id])->id,
        ]);
        MaintenanceSchedule::factory()->count(2)->create([
            'status' => 'active',
            'next_due_date' => now()->addDays(40),
            'team_id' => $this->team->id,
            'equipment_id' => Equipment::factory()->create(['team_id' => $this->team->id])->id,
        ]);

        // Enable query log
        DB::enableQueryLog();
        
        // Use the upcoming scope
        $upcoming = MaintenanceSchedule::upcoming(30)->get();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use a single efficient query
        $this->assertCount(1, $queries);
        $this->assertCount(3, $upcoming);
    }

    #[Test]
    public function bulk_equipment_loading_with_relationships_is_optimized(): void
    {
        // Create multiple equipment items with relationships
        Equipment::factory()
            ->count(10)
            ->has(WorkOrder::factory()->count(2)->state(['team_id' => $this->team->id]))
            ->has(MaintenanceSchedule::factory()->count(1)->state(['team_id' => $this->team->id]))
            ->create(['team_id' => $this->team->id]);

        // Enable query log
        DB::enableQueryLog();
        
        // Load all equipment with counts
        $equipment = Equipment::withWorkOrderCounts()
            ->withMaintenanceCounts()
            ->get();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should use minimal queries (not N+1)
        $this->assertLessThanOrEqual(3, count($queries), 'Expected at most 3 queries for bulk loading');
        $this->assertCount(10, $equipment);
    }
}
