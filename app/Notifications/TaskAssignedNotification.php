<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $taskType;

    public function __construct($task, $taskType = 'task')
    {
        $this->task = $task;
        $this->taskType = $taskType;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->getSubject();
        $greeting = "Hello {$notifiable->name}!";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($this->getDescription())
            ->line("Priority: " . ucfirst($this->task->priority ?? 'medium'))
            ->line("Due Date: " . ($this->task->due_date ?? $this->task->next_due_date ?? 'Not specified'))
            ->action('View Task', $this->getTaskUrl())
            ->line('Please complete this task as soon as possible.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_type' => $this->taskType,
            'title' => $this->getSubject(),
            'message' => $this->getDescription(),
            'priority' => $this->task->priority ?? 'medium',
            'due_date' => $this->task->due_date ?? $this->task->next_due_date ?? null,
            'url' => $this->getTaskUrl(),
        ];
    }

    protected function getSubject()
    {
        switch ($this->taskType) {
            case 'maintenance_schedule':
                return "Maintenance Schedule Assigned: {$this->task->name}";
            case 'work_order':
                return "Work Order Assigned: {$this->task->title}";
            default:
                return "Task Assigned: {$this->task->name}";
        }
    }

    protected function getDescription()
    {
        switch ($this->taskType) {
            case 'maintenance_schedule':
                $equipmentName = $this->task->equipment ? $this->task->equipment->name : 'N/A';
                return "You have been assigned a maintenance schedule: {$this->task->name}. Equipment: {$equipmentName}";
            case 'work_order':
                $location = $this->task->location ?? 'N/A';
                return "You have been assigned a work order: {$this->task->title}. Location: {$location}";
            default:
                return "You have been assigned a new task: {$this->task->name}";
        }
    }

    protected function getTaskUrl()
    {
        switch ($this->taskType) {
            case 'maintenance_schedule':
                return url("/admin/maintenance-schedules/{$this->task->id}/edit");
            case 'work_order':
                return url("/admin/work-orders/{$this->task->id}/edit");
            default:
                return url("/admin/tasks/{$this->task->id}/edit");
        }
    }
}