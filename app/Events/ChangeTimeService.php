<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use stdClass;
use Illuminate\Support\Facades\Log;

class ChangeTimeService implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesanan;

    public function __construct($pesanan)
    {
        // Convert stdClass to array if necessary
        if ($pesanan instanceof stdClass) {
            $pesanan = (array)$pesanan;
        }

        $this->pesanan = $pesanan;

        // Handle destination if it exists
        if (isset($this->pesanan['destination'])) {
            $destination = $this->pesanan['destination'];
            if (is_string($destination)) {
                // Handle potentially double-escaped JSON
                $decoded = json_decode($destination, true);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                if ($decoded && isset($decoded['lat'], $decoded['lng'])) {
                    $this->pesanan['destination'] = $decoded;
                } else {
                    Log::warning("Invalid destination format for pesanan ID {$this->pesanan['id_pesanan']}");
                    $this->pesanan['destination'] = null;
                }
            }
        }
    }

    public function broadcastOn(): array
    {
        $channel = new Channel('servicetime.' . $this->pesanan['id_pesanan']);
        return [$channel];
    }

    public function broadcastWith(): array
    {
        return [
            'id_pesanan' => $this->pesanan['id_pesanan'],
            'waktu_service' => $this->pesanan['waktu_servis'],
            'updated_at' => now()->toISOString(),
            'destination' => $this->pesanan['destination'],
        ];
    }
}