<div>
    <span class="unread-count">{{ $unreadCount }}</span>
    @foreach($notifications as $notification)
        <div class="notification" data-id="{{ $notification->id }}">
            {{ $notification->data['message'] ?? '' }}
        </div>
    @endforeach
</div>
