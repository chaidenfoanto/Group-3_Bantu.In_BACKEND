<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\PesananModel;
use Illuminate\Support\Facades\Log;

class ChangeTimeService implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesanan;

    public function __construct(PesananModel $pesanan)
    {
        $this->pesanan = $pesanan;

        // Pastikan destination adalah string ter-escape dan ubah menjadi array
        if (isset($this->pesanan->destination)) {
            $destination = json_decode($this->pesanan->destination, true);

            // Jika berhasil terdecode, simpan sebagai array
            if ($destination && isset($destination['lat'], $destination['lng'])) {
                $this->pesanan->destination = $destination;
            } else {
                // Jika gagal decode, Anda bisa melakukan penanganan error
                Log::warning("Invalid destination format for pesanan ID {$this->pesanan->id_pesanan}");
                // Anda bisa memberikan nilai default atau penanganan lain jika diperlukan
                $this->pesanan->destination = null;
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channel = new Channel('servicetime.' . $this->pesanan->id_pesanan);
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
            'id_pesanan' => $this->pesanan->id_pesanan,
            'waktu_service' => $this->pesanan->waktu_servis,
            'updated_at' => now()->toISOString(),
            // Pastikan destination sudah dalam bentuk array
            'destination' => $this->pesanan->destination, 
        ];
    }
}
