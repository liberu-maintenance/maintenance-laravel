<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\InventoryPart;
use App\Services\MaintenanceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class MaintenanceReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenanceReportService $service;
    protected Team $team;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MaintenanceReportService();
        
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
        $this->team->users()->attach($this->user);
    }

    public function test_calculate_mttr_with_completed_work_orders(): void
    {
        // Create work orders with known start and completion times
        $workOrder1 = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(4), // 24 hours
            'status' => 'completed',
        ]);

        $workOrder2 = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'started_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2)->subHours(12), // 36 hours
            'status' => 'completed',
        ]);

        $mttr = $this->service->calculateMTTR($this->team->id);

        // Average: (24 + 36) / 2 = 30 hours
        $this->assertEquals(30.0, $mttr);
    }

    public function test_calculate_mttr_returns_zero_when_no_completed_work_orders(): void
    {
        $mttr = $this->service->calculateMTTR($this->team->id);
        
        $this->assertEquals(0, $mttr);
    }

    public function test_calculate_mttr_filters_by_date_range(): void
    {
        // Work order within range
        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(9), // 24 hours
            'status' => 'completed',
        ]);

        // Work order outside range
        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'started_at' => now()->subDays(40),
            'completed_at' => now()->subDays(39), // 24 hours
            'status' => 'completed',
        ]);

        $startDate = now()->subDays(30);
        $endDate = now();

        $mttr = $this->service->calculateMTTR($this->team->id, $startDate, $endDate);

        // Should only count the work order within range
        $this->assertEquals(24.0, $mttr);
    }

    public function test_calculate_equipment_uptime(): void
    {
        $equipment = Equipment::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create a work order that took 3 days out of 30
        WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'team_id' => $this->team->id,
            'started_at' => now()->subDays(15),
            'completed_at' => now()->subDays(12), // 3 days
            'status' => 'completed',
        ]);

        $startDate = now()->subDays(30);
        $endDate = now();

        $uptime = $this->service->calculateEquipmentUptime($equipment->id, $startDate, $endDate);

        // Uptime: (30 - 3) / 30 * 100 = 90%
        $this->assertEquals(90.0, $uptime);
    }

    public function test_calculate_equipment_uptime_returns_100_when_no_maintenance(): void
    {
        $equipment = Equipment::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $startDate = now()->subDays(30);
        $endDate = now();

        $uptime = $this->service->calculateEquipmentUptime($equipment->id, $startDate, $endDate);

        $this->assertEquals(100.0, $uptime);
    }

    public function test_generate_cost_analysis(): void
    {
        $part1 = InventoryPart::factory()->create(['team_id' => $this->team->id]);
        $part2 = InventoryPart::factory()->create(['team_id' => $this->team->id]);

        $workOrder1 = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'completed_at' => now()->subDays(5),
            'actual_hours' => 10,
            'status' => 'completed',
        ]);

        $workOrder2 = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'completed_at' => now()->subDays(3),
            'actual_hours' => 5,
            'status' => 'completed',
        ]);

        // Attach parts with costs
        $workOrder1->inventoryParts()->attach($part1->id, [
            'quantity_used' => 2,
            'unit_cost' => 100,
        ]);

        $workOrder2->inventoryParts()->attach($part2->id, [
            'quantity_used' => 1,
            'unit_cost' => 50,
        ]);

        $costAnalysis = $this->service->generateCostAnalysis($this->team->id);

        // Parts cost: (2 * 100) + (1 * 50) = 250
        $this->assertEquals(250.0, $costAnalysis['parts_cost']);
        
        // Labor cost: (10 * 50) + (5 * 50) = 750
        $this->assertEquals(750.0, $costAnalysis['labor_cost']);
        
        // Total: 250 + 750 = 1000
        $this->assertEquals(1000.0, $costAnalysis['total_cost']);
        
        // Average: 1000 / 2 = 500
        $this->assertEquals(500.0, $costAnalysis['average_cost_per_work_order']);
        
        $this->assertEquals(2, $costAnalysis['total_work_orders']);
    }

    public function test_get_equipment_performance_metrics(): void
    {
        $equipment1 = Equipment::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Equipment 1',
            'criticality' => 'high',
        ]);

        $equipment2 = Equipment::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Equipment 2',
            'criticality' => 'critical',
        ]);

        // Create work orders for equipment1
        WorkOrder::factory()->count(3)->create([
            'equipment_id' => $equipment1->id,
            'team_id' => $this->team->id,
            'completed_at' => now()->subDays(5),
            'actual_hours' => 5,
            'status' => 'completed',
        ]);

        // Create work order for equipment2
        WorkOrder::factory()->create([
            'equipment_id' => $equipment2->id,
            'team_id' => $this->team->id,
            'completed_at' => now()->subDays(3),
            'actual_hours' => 10,
            'status' => 'completed',
        ]);

        $metrics = $this->service->getEquipmentPerformanceMetrics($this->team->id);

        $this->assertCount(2, $metrics);
        
        // Metrics should be sorted by total cost descending
        // Equipment2 should be first with cost: 10 * 50 = 500
        $this->assertEquals('Test Equipment 2', $metrics[0]['equipment_name']);
        $this->assertEquals(500.0, $metrics[0]['total_cost']);
        
        // Equipment1 should be second with cost: 3 * 5 * 50 = 750
        $this->assertEquals('Test Equipment 1', $metrics[1]['equipment_name']);
        $this->assertEquals(750.0, $metrics[1]['total_cost']);
    }

    public function test_get_technician_performance_metrics(): void
    {
        $tech1 = User::factory()->create(['name' => 'Tech 1']);
        $tech2 = User::factory()->create(['name' => 'Tech 2']);

        // Tech1: 3 completed out of 4 = 75% completion rate
        WorkOrder::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'assigned_to' => $tech1->id,
            'status' => 'completed',
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(9),
        ]);

        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'assigned_to' => $tech1->id,
            'status' => 'in_progress',
        ]);

        // Tech2: 1 completed out of 2 = 50% completion rate
        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'assigned_to' => $tech2->id,
            'status' => 'completed',
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(4),
        ]);

        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'assigned_to' => $tech2->id,
            'status' => 'pending',
        ]);

        $metrics = $this->service->getTechnicianPerformanceMetrics($this->team->id);

        $this->assertCount(2, $metrics);
        
        // Should be sorted by completion rate descending
        $this->assertEquals('Tech 1', $metrics[0]['technician_name']);
        $this->assertEquals(75.0, $metrics[0]['completion_rate']);
        $this->assertEquals(4, $metrics[0]['total_assigned']);
        $this->assertEquals(3, $metrics[0]['completed']);
        
        $this->assertEquals('Tech 2', $metrics[1]['technician_name']);
        $this->assertEquals(50.0, $metrics[1]['completion_rate']);
        $this->assertEquals(2, $metrics[1]['total_assigned']);
        $this->assertEquals(1, $metrics[1]['completed']);
    }

    public function test_analyze_maintenance_trends(): void
    {
        // Create work orders with different priorities
        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'submitted_at' => now()->subDays(5),
            'priority' => 'urgent',
            'status' => 'completed',
        ]);

        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'submitted_at' => now()->subDays(5),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'submitted_at' => now()->subDays(2),
            'priority' => 'medium',
            'status' => 'completed',
        ]);

        $trends = $this->service->analyzeMaintenanceTrends($this->team->id, 30);

        $this->assertArrayHasKey('daily_data', $trends);
        $this->assertArrayHasKey('week_over_week_change', $trends);
        $this->assertArrayHasKey('this_week_total', $trends);
        $this->assertArrayHasKey('last_week_total', $trends);
        $this->assertArrayHasKey('average_daily_work_orders', $trends);
        $this->assertArrayHasKey('peak_day', $trends);
    }

    public function test_generate_comprehensive_report(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);
        $tech = User::factory()->create();

        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'equipment_id' => $equipment->id,
            'assigned_to' => $tech->id,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(9),
            'actual_hours' => 8,
            'status' => 'completed',
        ]);

        $report = $this->service->generateComprehensiveReport($this->team->id);

        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('mttr', $report);
        $this->assertArrayHasKey('cost_analysis', $report);
        $this->assertArrayHasKey('equipment_performance', $report);
        $this->assertArrayHasKey('technician_performance', $report);
        $this->assertArrayHasKey('trends', $report);
        $this->assertArrayHasKey('actionable_insights', $report);

        // Verify period contains expected keys
        $this->assertArrayHasKey('start_date', $report['period']);
        $this->assertArrayHasKey('end_date', $report['period']);
        $this->assertArrayHasKey('days', $report['period']);
    }

    public function test_actionable_insights_detects_high_cost_equipment(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);
        
        // Create work orders with high cost
        $workOrder = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'equipment_id' => $equipment->id,
            'completed_at' => now()->subDays(5),
            'actual_hours' => 120, // 120 * $50 = $6000
            'status' => 'completed',
        ]);

        $report = $this->service->generateComprehensiveReport($this->team->id);
        $insights = $report['actionable_insights'];

        // Should detect high cost equipment (>$5000)
        $costInsight = collect($insights)->firstWhere('category', 'Cost Management');
        $this->assertNotNull($costInsight);
        $this->assertStringContainsString('exceeded $5,000', $costInsight['message']);
    }

    public function test_actionable_insights_detects_low_uptime(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);
        
        $startDate = now()->subDays(30);
        $endDate = now();
        
        // Create work order that takes 10 days (uptime = 66.67%)
        WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'equipment_id' => $equipment->id,
            'started_at' => now()->subDays(15),
            'completed_at' => now()->subDays(5), // 10 days down
            'status' => 'completed',
        ]);

        $report = $this->service->generateComprehensiveReport($this->team->id, $startDate, $endDate);
        $insights = $report['actionable_insights'];

        // Should detect low uptime (<80%)
        $uptimeInsight = collect($insights)->firstWhere('category', 'Equipment Reliability');
        $this->assertNotNull($uptimeInsight);
        $this->assertStringContainsString('uptime below 80%', $uptimeInsight['message']);
    }

    public function test_actionable_insights_detects_overdue_schedules(): void
    {
        MaintenanceSchedule::factory()->create([
            'team_id' => $this->team->id,
            'next_due_date' => now()->subDays(5),
            'status' => 'active',
        ]);

        $report = $this->service->generateComprehensiveReport($this->team->id);
        $insights = $report['actionable_insights'];

        // Should detect overdue schedules
        $scheduleInsight = collect($insights)->firstWhere('category', 'Preventive Maintenance');
        $this->assertNotNull($scheduleInsight);
        $this->assertStringContainsString('overdue', $scheduleInsight['message']);
    }
}
