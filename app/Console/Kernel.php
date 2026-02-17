<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check for overdue and upcoming maintenance daily at 8 AM
        $schedule->command('maintenance:check-due --days=7')
            ->dailyAt('08:00')
            ->timezone('UTC')
            ->name('Check due maintenance schedules')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Maintenance check completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Maintenance check failed');
            });

        // Send reminders for maintenance due in 3 days
        $schedule->command('maintenance:send-reminders --days=3')
            ->dailyAt('09:00')
            ->timezone('UTC')
            ->name('Send 3-day maintenance reminders')
            ->withoutOverlapping();

        // Send reminders for maintenance due in 1 day
        $schedule->command('maintenance:send-reminders --days=1')
            ->dailyAt('09:00')
            ->timezone('UTC')
            ->name('Send 1-day maintenance reminders')
            ->withoutOverlapping();

        // Send reminders for maintenance due today (morning reminder)
        $schedule->command('maintenance:send-reminders --days=0')
            ->dailyAt('07:00')
            ->timezone('UTC')
            ->name('Send today\'s maintenance reminders')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
