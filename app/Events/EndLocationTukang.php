<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Event\ShouldBroadcastNow;
use App\Models\LocationModel;
use App\Models\User;

class EndLocationTukang
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $locate;
    private $user;

    /**
     * Create a new event instance.
     */
    public function __construct(LocationModel $locate, User $user)
    {
        $this->locate = $locate;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('passeger_' . $this->user->id),
        ];
    }
}
