<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\TukangModel;

class UpdatedLocationTukang implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tukang;

    /**
     * Create a new event instance.
     *
     * @param TukangModel $tukang
     * @return void
     */
    public function __construct(TukangModel $tukang)
    {
        $this->tukang = $tukang;
        \Log::info('UpdatedLocationTukang event constructed', [
            'tukang_id' => $tukang->id_tukang,
            'location' => $tukang->tukang_location
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channel = new Channel('tukang.' . $this->tukang->id_tukang);
        \Log::info('Broadcasting location update', [
            'channel' => $channel->name,
            'tukang_id' => $this->tukang->id_tukang
        ]);
        return [$channel];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'tukang_id' => $this->tukang->id_tukang,
            'tukang_location' => $this->tukang->tukang_location,
            'updated_at' => now()->toISOString()
        ];
    }
}