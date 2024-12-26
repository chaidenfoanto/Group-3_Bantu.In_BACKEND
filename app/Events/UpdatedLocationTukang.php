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
use App\Models\TukangModel;

class UpdatedLocationTukang 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;

    /**
     * Create a new event instance.
     */
    public function __construct(TukangModel $tukang)
    {
        $this->tukang = $tukang;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tukang.' . $this->tukang->id_tukang),
        ];
    }

    public function broadcastWith()
    {
        // Menyertakan data lokasi tukang terbaru
        return [
            'tukang_location' => $this->tukang->tukang_location,
        ];
    }
}
