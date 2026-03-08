<?php

namespace Tests\Feature;

use App\Console\Commands\CheckDueMaintenanceCommand;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use App\Models\User;
use App\Notifications\MaintenanceDueSoonNotification;
use App\Notifications\MaintenanceOverdueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckDueMaintenanceCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->equipment = Equipment::factory()->create([
            'team_id' => $this->team->id,
        ]);

        Notification::fake();
    }

    #[Test]
    public function it_detects_overdue_maintenance_schedules(): void
    {
        $overdueSchedule = MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->subDays(5),
        ]);

        $this->artisan(CheckDueMaintenanceCommand::class)
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MaintenanceOverdueNotification::class);
    }

    #[Test]
    public function it_detects_maintenance_due_soon(): void
    {
        $dueSoonSchedule = MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->addDays(3),
        ]);

        $this->artisan(CheckDueMaintenanceCommand::class, ['--days' => 7])
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MaintenanceDueSoonNotification::class);
    }

    #[Test]
    public function it_does_not_notify_for_inactive_schedules(): void
    {
        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'inactive',
            'next_due_date' => now()->subDays(5),
        ]);

        $this->artisan(CheckDueMaintenanceCommand::class)
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MaintenanceOverdueNotification::class);
    }

    #[Test]
    public function it_does_not_notify_when_no_user_is_assigned(): void
    {
        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => null,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->subDays(5),
        ]);

        $this->artisan(CheckDueMaintenanceCommand::class)
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_respects_custom_days_ahead_parameter(): void
    {
        // Create schedule due in 10 days
        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->addDays(10),
        ]);

        // Should not be detected with default 7 days
        $this->artisan(CheckDueMaintenanceCommand::class, ['--days' => 7])
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MaintenanceDueSoonNotification::class);

        // Reset notifications
        Notification::fake();

        // Should be detected with 14 days
        $this->artisan(CheckDueMaintenanceCommand::class, ['--days' => 14])
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MaintenanceDueSoonNotification::class);
    }

    #[Test]
    public function it_notifies_multiple_users_for_different_schedules(): void
    {
        $user2 = User::factory()->create();
        $equipment2 = Equipment::factory()->create(['team_id' => $this->team->id]);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->subDays(2),
        ]);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $equipment2->id,
            'assigned_to' => $user2->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->subDays(3),
        ]);

        $this->artisan(CheckDueMaintenanceCommand::class)
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MaintenanceOverdueNotification::class);
        Notification::assertSentTo($user2, MaintenanceOverdueNotification::class);
    }
}
