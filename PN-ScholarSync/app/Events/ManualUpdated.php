<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ManualUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $updateType;
    public $updateDetails;

    /**
     * Create a new event instance.
     *
     * @param string $updateType - Type of update (manual_update, new_violation_type, category_change)
     * @param array $updateDetails - Details about what was updated
     * @return void
     */
    public function __construct($updateType, $updateDetails = [])
    {
        $this->updateType = $updateType;
        $this->updateDetails = $updateDetails;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('manual-updates');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'update_type' => $this->updateType,
            'update_details' => $this->updateDetails,
            'timestamp' => now()->timestamp
        ];
    }
}
