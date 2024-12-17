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

class EndLocationTukang implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $locate;
    private $user;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(LocationModel $locate, User $user, $message)
    {
        $this->locate = $locate;
        $this->user = $user;
        $this->message = $message;
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

    public function broadcastAs() {
        return 'endLocationTukang';
    }

    public function broadcastWith() {
        return [
            "message" => "Tukang has been slain"
        ];
    }
}
