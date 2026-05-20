<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenanceSchedule;

    public function __construct(MaintenanceSchedule $maintenanceSchedule)
    {
        $this->maintenanceSchedule = $maintenanceSchedule;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $daysOverdue = now()->diffInDays($this->maintenanceSchedule->next_due_date);

        return (new MailMessage)
            ->subject("OVERDUE: Maintenance Required - {$this->maintenanceSchedule->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("The following maintenance is overdue by {$daysOverdue} days:")
            ->line("Equipment: {$this->maintenanceSchedule->equipment->name}")
            ->line("Maintenance: {$this->maintenanceSchedule->name}")
            ->line("Due Date: {$this->maintenanceSchedule->next_due_date->format('Y-m-d')}")
            ->line("Priority: " . ucfirst($this->maintenanceSchedule->priority))
            ->action('View Maintenance Schedule', url("/admin/maintenance-schedules/{$this->maintenanceSchedule->id}/edit"))
            ->line('Please complete this maintenance immediately to avoid equipment failure.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'title' => "OVERDUE: {$this->maintenanceSchedule->name}",
            'message' => "Maintenance for {$this->maintenanceSchedule->equipment->name} is overdue",
            'priority' => 'critical',
            'due_date' => $this->maintenanceSchedule->next_due_date,
            'url' => url("/admin/maintenance-schedules/{$this->maintenanceSchedule->id}/edit"),
        ];
    }
}