<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenanceSchedule;
    protected $daysUntilDue;

    public function __construct(MaintenanceSchedule $maintenanceSchedule, $daysUntilDue = 3)
    {
        $this->maintenanceSchedule = $maintenanceSchedule;
        $this->daysUntilDue = $daysUntilDue;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $daysText = $this->daysUntilDue == 1 ? 'tomorrow' : "in {$this->daysUntilDue} days";
        $priorityEmoji = $this->maintenanceSchedule->priority === 'critical' ? '🚨 ' : 
                        ($this->maintenanceSchedule->priority === 'high' ? '⚠️ ' : '');

        return (new MailMessage)
            ->subject("{$priorityEmoji}Reminder: Maintenance Due {$daysText} - {$this->maintenanceSchedule->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a friendly reminder that the following maintenance is scheduled {$daysText}:")
            ->line("Equipment: {$this->maintenanceSchedule->equipment->name}")
            ->line("Maintenance: {$this->maintenanceSchedule->name}")
            ->line("Due Date: {$this->maintenanceSchedule->next_due_date->format('l, F j, Y')}")
            ->line("Priority: " . ucfirst($this->maintenanceSchedule->priority))
            ->when($this->maintenanceSchedule->estimated_duration, function ($message) {
                return $message->line("Estimated Duration: {$this->maintenanceSchedule->estimated_duration} minutes");
            })
            ->when($this->maintenanceSchedule->instructions, function ($message) {
                return $message->line("Instructions:")
                              ->line($this->maintenanceSchedule->instructions);
            })
            ->when($this->maintenanceSchedule->checklist, function ($message) {
                return $message->line("Checklist: {$this->maintenanceSchedule->checklist->name}");
            })
            ->action('View Maintenance Details', url("/admin/maintenance-schedules/{$this->maintenanceSchedule->id}/edit"))
            ->line('Thank you for keeping our equipment in top condition!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'title' => "Reminder: {$this->maintenanceSchedule->name}",
            'message' => "Maintenance for {$this->maintenanceSchedule->equipment->name} is due in {$this->daysUntilDue} days",
            'priority' => $this->maintenanceSchedule->priority,
            'due_date' => $this->maintenanceSchedule->next_due_date,
            'days_until_due' => $this->daysUntilDue,
            'url' => url("/admin/maintenance-schedules/{$this->maintenanceSchedule->id}/edit"),
        ];
    }
}
