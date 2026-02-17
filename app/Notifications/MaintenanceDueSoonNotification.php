<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $maintenanceSchedule;
    public $daysUntilDue;

    public function __construct(MaintenanceSchedule $maintenanceSchedule, $daysUntilDue = 7)
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
        $daysText = $this->daysUntilDue == 1 ? '1 day' : "{$this->daysUntilDue} days";

        return (new MailMessage)
            ->subject("Upcoming Maintenance: {$this->maintenanceSchedule->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("The following maintenance is due in {$daysText}:")
            ->line("Equipment: {$this->maintenanceSchedule->equipment->name}")
            ->line("Maintenance: {$this->maintenanceSchedule->name}")
            ->line("Due Date: {$this->maintenanceSchedule->next_due_date->format('Y-m-d')}")
            ->line("Priority: " . ucfirst($this->maintenanceSchedule->priority))
            ->when($this->maintenanceSchedule->estimated_duration, function ($message) {
                return $message->line("Estimated Duration: {$this->maintenanceSchedule->estimated_duration} minutes");
            })
            ->when($this->maintenanceSchedule->instructions, function ($message) {
                return $message->line("Instructions: {$this->maintenanceSchedule->instructions}");
            })
            ->action('View Maintenance Schedule', url("/admin/maintenance-schedules/{$this->maintenanceSchedule->id}/edit"))
            ->line('Please plan accordingly to complete this maintenance on time.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'title' => "Upcoming: {$this->maintenanceSchedule->name}",
            'message' => "Maintenance for {$this->maintenanceSchedule->equipment->name} is due in {$this->daysUntilDue} days",
            'priority' => $this->maintenanceSchedule->priority,
            'due_date' => $this->maintenanceSchedule->next_due_date,
            'days_until_due' => $this->daysUntilDue,
            'url' => url("/admin/maintenance-schedules/{$this->maintenanceSchedule->id}/edit"),
        ];
    }
}
