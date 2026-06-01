<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Http\Livewire\MaintenanceDashboard;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaintenanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->team = Team::factory()->create();
        $this->user = User::factory()->create(['current_team_id' => $this->team->id]);
        $this->team->users()->attach($this->user);
    }

    #[Test]
    public function component_renders_successfully(): void
    {
        $this->actingAs($this->user);

        Livewire::test(MaintenanceDashboard::class)
            ->assertStatus(200);
    }

    #[Test]
    public function component_loads_overdue_maintenance(): void
    {
        $this->actingAs($this->user);

        // Create an overdue maintenance schedule
        MaintenanceSchedule::factory()->create([
            'status'        => 'active',
            'next_due_date' => now()->subDays(5),
        ]);

        $component = Livewire::test(MaintenanceDashboard::class);

        $component->assertSet('overdueMaintenance', function ($overdue) {
            return count($overdue) >= 1;
        });
    }

    #[Test]
    public function component_loads_pending_work_orders(): void
    {
        $this->actingAs($this->user);

        WorkOrder::factory()->count(2)->create(['status' => 'pending']);

        $component = Livewire::test(MaintenanceDashboard::class);

        $component->assertSet('pendingWorkOrders', function ($pending) {
            return count($pending) >= 2;
        });
    }

    #[Test]
    public function stats_include_expected_keys(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(MaintenanceDashboard::class);

        $component->assertSet('stats', function ($stats) {
            return array_key_exists('total_equipment', $stats)
                && array_key_exists('overdue_maintenance', $stats)
                && array_key_exists('pending_work_orders', $stats)
                && array_key_exists('completed_this_month', $stats);
        });
    }

    #[Test]
    public function component_can_approve_a_work_order(): void
    {
        $this->actingAs($this->user);

        $workOrder = WorkOrder::factory()->create(['status' => 'pending']);

        Livewire::test(MaintenanceDashboard::class)
            ->call('approveWorkOrder', $workOrder->id);

        $this->assertDatabaseHas('work_orders', [
            'id'     => $workOrder->id,
            'status' => 'approved',
        ]);
    }
}
