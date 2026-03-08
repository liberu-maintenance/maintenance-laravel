<?php

namespace Tests\Feature;

use App\Console\Commands\SendMaintenanceRemindersCommand;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use App\Models\User;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendMaintenanceRemindersCommandTest extends TestCase
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
    public function it_sends_reminders_for_maintenance_due_in_3_days(): void
    {
        $targetDate = now()->addDays(3);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 3])
            ->expectsOutput("Sending reminders for maintenance due on {$targetDate->format('Y-m-d')}...")
            ->assertSuccessful();

        Notification::assertSentTo(
            $this->user,
            MaintenanceReminderNotification::class,
            function ($notification) {
                return $notification->daysUntilDue === 3;
            }
        );
    }

    #[Test]
    public function it_sends_reminders_for_maintenance_due_tomorrow(): void
    {
        $targetDate = now()->addDay();

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 1])
            ->assertSuccessful();

        Notification::assertSentTo(
            $this->user,
            MaintenanceReminderNotification::class,
            function ($notification) {
                return $notification->daysUntilDue === 1;
            }
        );
    }

    #[Test]
    public function it_sends_reminders_for_maintenance_due_today(): void
    {
        $targetDate = now();

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 0])
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MaintenanceReminderNotification::class);
    }

    #[Test]
    public function it_does_not_send_reminders_for_inactive_schedules(): void
    {
        $targetDate = now()->addDays(3);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'inactive',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 3])
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MaintenanceReminderNotification::class);
    }

    #[Test]
    public function it_does_not_send_reminders_when_no_user_is_assigned(): void
    {
        $targetDate = now()->addDays(3);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => null,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 3])
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_does_not_send_reminders_for_wrong_dates(): void
    {
        // Schedule due in 5 days, but command is for 3 days
        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => now()->addDays(5),
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 3])
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MaintenanceReminderNotification::class);
    }

    #[Test]
    public function it_sends_reminders_to_multiple_users(): void
    {
        $user2 = User::factory()->create();
        $equipment2 = Equipment::factory()->create(['team_id' => $this->team->id]);
        $targetDate = now()->addDays(3);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        MaintenanceSchedule::factory()->create([
            'equipment_id' => $equipment2->id,
            'assigned_to' => $user2->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 3])
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MaintenanceReminderNotification::class);
        Notification::assertSentTo($user2, MaintenanceReminderNotification::class);
    }

    #[Test]
    public function it_displays_correct_summary_information(): void
    {
        $targetDate = now()->addDays(3);

        MaintenanceSchedule::factory()->count(3)->create([
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'status' => 'active',
            'next_due_date' => $targetDate,
        ]);

        $this->artisan(SendMaintenanceRemindersCommand::class, ['--days' => 3])
            ->expectsOutput('Found 3 maintenance schedule(s) due in 3 days.')
            ->expectsOutput('Reminder process completed!')
            ->expectsOutput('  Sent: 3')
            ->assertSuccessful();
    }
}
