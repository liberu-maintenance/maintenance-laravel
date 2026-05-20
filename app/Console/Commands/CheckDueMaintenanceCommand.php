<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Notifications\MaintenanceOverdueNotification;
use App\Notifications\MaintenanceDueSoonNotification;
use Illuminate\Console\Command;

class CheckDueMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check-due
                          {--days=7 : Number of days to look ahead for due maintenance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue and upcoming maintenance schedules and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for due maintenance schedules...');

        $daysAhead = (int) $this->option('days');
        
        // Check for overdue maintenance
        $overdueMaintenance = MaintenanceSchedule::overdue()->get();
        
        if ($overdueMaintenance->isNotEmpty()) {
            $this->warn("Found {$overdueMaintenance->count()} overdue maintenance schedules.");
            
            foreach ($overdueMaintenance as $schedule) {
                if ($schedule->assignedUser) {
                    $schedule->assignedUser->notify(new MaintenanceOverdueNotification($schedule));
                    $this->line("  - Notified {$schedule->assignedUser->name} about overdue: {$schedule->name}");
                } else {
                    $this->warn("  - No assigned user for overdue maintenance: {$schedule->name}");
                }
            }
        } else {
            $this->info('No overdue maintenance found.');
        }

        // Check for maintenance due soon
        $dueSoonMaintenance = MaintenanceSchedule::dueSoon($daysAhead)->get();
        
        if ($dueSoonMaintenance->isNotEmpty()) {
            $this->info("Found {$dueSoonMaintenance->count()} maintenance schedules due within {$daysAhead} days.");
            
            foreach ($dueSoonMaintenance as $schedule) {
                if ($schedule->assignedUser) {
                    $schedule->assignedUser->notify(new MaintenanceDueSoonNotification($schedule, $daysAhead));
                    $this->line("  - Notified {$schedule->assignedUser->name} about upcoming: {$schedule->name}");
                } else {
                    $this->warn("  - No assigned user for upcoming maintenance: {$schedule->name}");
                }
            }
        } else {
            $this->info("No maintenance due within {$daysAhead} days.");
        }

        $this->info('Maintenance check completed successfully!');
        
        return Command::SUCCESS;
    }
}
