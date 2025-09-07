<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Notifications\DatabaseNotification;

class TaskNotifications extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDropdown = false;

    protected $listeners = [
        'notificationRead' => 'loadNotifications',
        'echo:notifications,NotificationSent' => 'handleNewNotification',
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = auth()->user()
            ->notifications()
            ->latest()
            ->limit(10)
            ->get();

        $this->unreadCount = auth()->user()
            ->unreadNotifications()
            ->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = DatabaseNotification::find($notificationId);
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->emit('notificationRead');
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
        $this->emit('notificationRead');
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function handleNewNotification($event)
    {
        $this->loadNotifications();
        $this->emit('newNotification', $event);
    }

    public function render()
    {
        return view('livewire.task-notifications');
    }
}