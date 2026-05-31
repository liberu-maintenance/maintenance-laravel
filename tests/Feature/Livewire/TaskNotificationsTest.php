<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Http\Livewire\TaskNotifications;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[Test]
    public function component_renders_successfully(): void
    {
        $this->actingAs($this->user);

        Livewire::test(TaskNotifications::class)
            ->assertStatus(200);
    }

    #[Test]
    public function unread_count_is_zero_when_no_notifications(): void
    {
        $this->actingAs($this->user);

        Livewire::test(TaskNotifications::class)
            ->assertSet('unreadCount', 0);
    }

    #[Test]
    public function unread_count_reflects_unread_notifications(): void
    {
        $this->actingAs($this->user);

        // Create 2 unread database notifications
        $this->user->notifications()->create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['message' => 'First notification'],
            'read_at'         => null,
        ]);

        $this->user->notifications()->create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['message' => 'Second notification'],
            'read_at'         => null,
        ]);

        Livewire::test(TaskNotifications::class)
            ->assertSet('unreadCount', 2);
    }

    #[Test]
    public function mark_as_read_decrements_unread_count(): void
    {
        $this->actingAs($this->user);

        $notificationId = \Illuminate\Support\Str::uuid();

        $this->user->notifications()->create([
            'id'              => $notificationId,
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => ['message' => 'Unread'],
            'read_at'         => null,
        ]);

        Livewire::test(TaskNotifications::class)
            ->assertSet('unreadCount', 1)
            ->call('markAsRead', $notificationId)
            ->assertSet('unreadCount', 0);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
        ]);

        $notification = DatabaseNotification::find($notificationId);
        $this->assertNotNull($notification->read_at);
    }

    #[Test]
    public function mark_all_as_read_clears_unread_count(): void
    {
        $this->actingAs($this->user);

        foreach (range(1, 3) as $i) {
            $this->user->notifications()->create([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'App\Notifications\TestNotification',
                'notifiable_type' => User::class,
                'notifiable_id'   => $this->user->id,
                'data'            => ['message' => "Notification {$i}"],
                'read_at'         => null,
            ]);
        }

        Livewire::test(TaskNotifications::class)
            ->assertSet('unreadCount', 3)
            ->call('markAllAsRead')
            ->assertSet('unreadCount', 0);
    }
}
