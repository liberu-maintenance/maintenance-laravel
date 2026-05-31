<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Checklist;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaintenanceScheduleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_expected_fillable_attributes(): void
    {
        $schedule = new MaintenanceSchedule();
        $fillable = $schedule->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('equipment_id', $fillable);
        $this->assertContains('frequency_type', $fillable);
        $this->assertContains('frequency_value', $fillable);
        $this->assertContains('next_due_date', $fillable);
        $this->assertContains('team_id', $fillable);
        $this->assertContains('status', $fillable);
    }

    #[Test]
    public function it_belongs_to_equipment(): void
    {
        $equipment = Equipment::factory()->create();
        $schedule  = MaintenanceSchedule::factory()->create(['equipment_id' => $equipment->id]);

        $this->assertInstanceOf(Equipment::class, $schedule->equipment);
        $this->assertEquals($equipment->id, $schedule->equipment->id);
    }

    #[Test]
    public function it_belongs_to_a_team(): void
    {
        $team     = Team::factory()->create();
        $schedule = MaintenanceSchedule::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $schedule->team);
        $this->assertEquals($team->id, $schedule->team->id);
    }

    #[Test]
    public function it_belongs_to_an_assigned_user(): void
    {
        $user     = User::factory()->create();
        $schedule = MaintenanceSchedule::factory()->create(['assigned_to' => $user->id]);

        $this->assertInstanceOf(User::class, $schedule->assignedUser);
        $this->assertEquals($user->id, $schedule->assignedUser->id);
    }

    #[Test]
    public function it_belongs_to_a_checklist(): void
    {
        $checklist = Checklist::factory()->create();
        $schedule  = MaintenanceSchedule::factory()->create(['checklist_id' => $checklist->id]);

        $this->assertInstanceOf(Checklist::class, $schedule->checklist);
    }

    #[Test]
    public function it_has_many_work_orders(): void
    {
        $schedule = MaintenanceSchedule::factory()->create();
        WorkOrder::factory()->count(2)->create(['maintenance_schedule_id' => $schedule->id]);

        $this->assertCount(2, $schedule->workOrders);
    }

    #[Test]
    public function overdue_scope_returns_active_past_due_schedules(): void
    {
        // Overdue: active and past due
        MaintenanceSchedule::factory()->create([
            'status'        => 'active',
            'next_due_date' => now()->subDays(3),
        ]);

        // Not overdue: inactive and past due
        MaintenanceSchedule::factory()->create([
            'status'        => 'inactive',
            'next_due_date' => now()->subDays(3),
        ]);

        // Not overdue: active and future
        MaintenanceSchedule::factory()->create([
            'status'        => 'active',
            'next_due_date' => now()->addDays(5),
        ]);

        $overdue = MaintenanceSchedule::overdue()->get();

        $this->assertCount(1, $overdue);
    }

    #[Test]
    public function due_soon_scope_returns_schedules_due_within_given_days(): void
    {
        // Due soon: active and within 7 days
        MaintenanceSchedule::factory()->create([
            'status'        => 'active',
            'next_due_date' => now()->addDays(3),
        ]);

        // Not due soon: active but too far in future
        MaintenanceSchedule::factory()->create([
            'status'        => 'active',
            'next_due_date' => now()->addDays(30),
        ]);

        // Not due soon: past due
        MaintenanceSchedule::factory()->create([
            'status'        => 'active',
            'next_due_date' => now()->subDays(1),
        ]);

        $dueSoon = MaintenanceSchedule::dueSoon(7)->get();

        $this->assertCount(1, $dueSoon);
    }

    #[Test]
    public function active_scope_returns_only_active_schedules(): void
    {
        MaintenanceSchedule::factory()->count(3)->create(['status' => 'active']);
        MaintenanceSchedule::factory()->count(2)->create(['status' => 'inactive']);

        $active = MaintenanceSchedule::active()->get();

        $this->assertCount(3, $active);
    }

    #[Test]
    public function calculate_next_due_date_returns_current_next_due_date_when_not_completed(): void
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'frequency_type'      => 'monthly',
            'frequency_value'     => 1,
            'next_due_date'       => now()->addDays(10),
            'last_completed_date' => null,
        ]);

        $nextDueDate = $schedule->calculateNextDueDate();

        $this->assertEquals(
            $schedule->next_due_date->format('Y-m-d'),
            $nextDueDate->format('Y-m-d')
        );
    }

    #[Test]
    public function calculate_next_due_date_adds_frequency_when_completed(): void
    {
        $lastCompleted = now()->subDays(5);

        $schedule = MaintenanceSchedule::factory()->create([
            'frequency_type'      => 'weekly',
            'frequency_value'     => 2,
            'last_completed_date' => $lastCompleted,
        ]);

        $nextDueDate = $schedule->calculateNextDueDate();

        $expected = $lastCompleted->addWeeks(2);

        $this->assertEquals($expected->format('Y-m-d'), $nextDueDate->format('Y-m-d'));
    }
}
