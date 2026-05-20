<?php

namespace Tests\Unit\Notifications;

use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Team;
use App\Models\User;
use App\Notifications\MaintenanceDueSoonNotification;
use App\Notifications\MaintenanceOverdueNotification;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaintenanceNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Equipment $equipment;
    protected MaintenanceSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'John Doe']);
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->equipment = Equipment::factory()->create([
            'name' => 'Test Equipment',
            'team_id' => $this->team->id,
        ]);
        $this->schedule = MaintenanceSchedule::factory()->create([
            'name' => 'Test Maintenance',
            'equipment_id' => $this->equipment->id,
            'assigned_to' => $this->user->id,
            'team_id' => $this->team->id,
            'priority' => 'high',
            'next_due_date' => now()->addDays(5),
            'estimated_duration' => 60,
            'instructions' => 'Test instructions',
        ]);
    }

    #[Test]
    public function maintenance_overdue_notification_has_correct_channels(): void
    {
        $notification = new MaintenanceOverdueNotification($this->schedule);

        $this->assertEquals(['mail', 'database'], $notification->via($this->user));
    }

    #[Test]
    public function maintenance_overdue_notification_mail_has_correct_content(): void
    {
        $this->schedule->update(['next_due_date' => now()->subDays(5)]);
        $notification = new MaintenanceOverdueNotification($this->schedule);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('OVERDUE: Maintenance Required', $mail->subject);
        $this->assertStringContainsString('Test Equipment', $mail->render());
        $this->assertStringContainsString('Test Maintenance', $mail->render());
        $this->assertStringContainsString('High', $mail->render());
    }

    #[Test]
    public function maintenance_overdue_notification_database_has_correct_data(): void
    {
        $notification = new MaintenanceOverdueNotification($this->schedule);

        $data = $notification->toDatabase($this->user);

        $this->assertEquals($this->schedule->id, $data['maintenance_schedule_id']);
        $this->assertEquals('critical', $data['priority']);
        $this->assertStringContainsString('OVERDUE', $data['title']);
        $this->assertArrayHasKey('url', $data);
    }

    #[Test]
    public function maintenance_due_soon_notification_has_correct_channels(): void
    {
        $notification = new MaintenanceDueSoonNotification($this->schedule, 7);

        $this->assertEquals(['mail', 'database'], $notification->via($this->user));
    }

    #[Test]
    public function maintenance_due_soon_notification_mail_has_correct_content(): void
    {
        $notification = new MaintenanceDueSoonNotification($this->schedule, 7);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('Upcoming Maintenance', $mail->subject);
        $this->assertStringContainsString('Test Equipment', $mail->render());
        $this->assertStringContainsString('7 days', $mail->render());
        $this->assertStringContainsString('60 minutes', $mail->render());
        $this->assertStringContainsString('Test instructions', $mail->render());
    }

    #[Test]
    public function maintenance_due_soon_notification_singular_day_text(): void
    {
        $notification = new MaintenanceDueSoonNotification($this->schedule, 1);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('1 day', $mail->render());
    }

    #[Test]
    public function maintenance_due_soon_notification_database_has_correct_data(): void
    {
        $notification = new MaintenanceDueSoonNotification($this->schedule, 7);

        $data = $notification->toDatabase($this->user);

        $this->assertEquals($this->schedule->id, $data['maintenance_schedule_id']);
        $this->assertEquals('high', $data['priority']);
        $this->assertEquals(7, $data['days_until_due']);
        $this->assertStringContainsString('Upcoming', $data['title']);
    }

    #[Test]
    public function maintenance_reminder_notification_has_correct_channels(): void
    {
        $notification = new MaintenanceReminderNotification($this->schedule, 3);

        $this->assertEquals(['mail', 'database'], $notification->via($this->user));
    }

    #[Test]
    public function maintenance_reminder_notification_mail_has_correct_content(): void
    {
        $notification = new MaintenanceReminderNotification($this->schedule, 3);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('Reminder', $mail->subject);
        $this->assertStringContainsString('Test Maintenance', $mail->subject);
        $this->assertStringContainsString('Test Equipment', $mail->render());
        $this->assertStringContainsString('in 3 days', $mail->render());
        $this->assertStringContainsString('High', $mail->render());
        $this->assertStringContainsString('60 minutes', $mail->render());
        $this->assertStringContainsString('Test instructions', $mail->render());
    }

    #[Test]
    public function maintenance_reminder_notification_tomorrow_text(): void
    {
        $notification = new MaintenanceReminderNotification($this->schedule, 1);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('tomorrow', $mail->render());
    }

    #[Test]
    public function maintenance_reminder_notification_critical_priority_has_emoji(): void
    {
        $this->schedule->update(['priority' => 'critical']);
        $notification = new MaintenanceReminderNotification($this->schedule, 3);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('🚨', $mail->subject);
    }

    #[Test]
    public function maintenance_reminder_notification_high_priority_has_emoji(): void
    {
        $this->schedule->update(['priority' => 'high']);
        $notification = new MaintenanceReminderNotification($this->schedule, 3);

        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('⚠️', $mail->subject);
    }

    #[Test]
    public function maintenance_reminder_notification_database_has_correct_data(): void
    {
        $notification = new MaintenanceReminderNotification($this->schedule, 3);

        $data = $notification->toDatabase($this->user);

        $this->assertEquals($this->schedule->id, $data['maintenance_schedule_id']);
        $this->assertEquals('high', $data['priority']);
        $this->assertEquals(3, $data['days_until_due']);
        $this->assertStringContainsString('Reminder', $data['title']);
        $this->assertArrayHasKey('url', $data);
    }

    #[Test]
    public function notifications_include_checklist_when_available(): void
    {
        $checklist = \App\Models\Checklist::factory()->create([
            'name' => 'Test Checklist',
            'team_id' => $this->team->id,
        ]);

        $this->schedule->update(['checklist_id' => $checklist->id]);
        $this->schedule->refresh();

        $notification = new MaintenanceReminderNotification($this->schedule, 3);
        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('Test Checklist', $mail->render());
    }
}
