<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public Notification $notification;

	public function __construct(Notification $notification)
	{
		$this->notification = $notification;
	}

	public function broadcastOn(): Channel
	{
		return new PrivateChannel('users.' . $this->notification->user_id . '.notifications');
	}

	public function broadcastAs(): string
	{
		return 'notification.created';
	}

	public function broadcastWith(): array
	{
		return [
			'id' => $this->notification->id,
			'user_id' => $this->notification->user_id,
			'title' => $this->notification->title,
			'message' => $this->notification->message,
			'type' => $this->notification->type,
			'is_read' => $this->notification->is_read,
			'related_id' => $this->notification->related_id,
			'created_at' => optional($this->notification->created_at)->toDateTimeString(),
		];
	}
}
