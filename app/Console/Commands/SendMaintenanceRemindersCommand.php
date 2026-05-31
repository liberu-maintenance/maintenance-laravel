<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Console\Command;

#[\Illuminate\Console\Attributes\Description('Send reminder notifications for maintenance schedules due soon')]
#[\Illuminate\Console\Attributes\Signature('maintenance:send-reminders
                          {--days=3 : Number of days before due date to send reminders}')]
class SendMaintenanceRemindersCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminderDays = (int) $this->option('days');
        $targetDate = now()->addDays($reminderDays);
        
        $this->info("Sending reminders for maintenance due on " . $targetDate->format('Y-m-d') . "...");

        // Get maintenance schedules due on the target date
        $maintenanceSchedules = MaintenanceSchedule::query()
            ->where('status', 'active')
            ->whereDate('next_due_date', '=', $targetDate->format('Y-m-d'))
            ->with(['assignedUser', 'equipment'])
            ->get();

        if ($maintenanceSchedules->isEmpty()) {
            $this->info("No maintenance schedules due in {$reminderDays} days.");
            return Command::SUCCESS;
        }

        $this->info("Found {$maintenanceSchedules->count()} maintenance schedule(s) due in {$reminderDays} days.");

        $notificationsSent = 0;
        $notificationsFailed = 0;

        foreach ($maintenanceSchedules as $schedule) {
            if ($schedule->assignedUser) {
                try {
                    $schedule->assignedUser->notify(new MaintenanceReminderNotification($schedule, $reminderDays));
                    $this->line("  ✓ Reminded {$schedule->assignedUser->name} about: {$schedule->name}");
                    $notificationsSent++;
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to notify {$schedule->assignedUser->name}: {$e->getMessage()}");
                    $notificationsFailed++;
                }
            } else {
                $this->warn("  - No assigned user for: {$schedule->name}");
                $notificationsFailed++;
            }
        }

        $this->newLine();
        $this->info("Reminder process completed!");
        $this->info("  Sent: {$notificationsSent}");
        
        if ($notificationsFailed > 0) {
            $this->warn("  Failed/Skipped: {$notificationsFailed}");
        }

        return Command::SUCCESS;
    }
}
