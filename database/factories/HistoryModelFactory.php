<?php

namespace Database\Factories;

use App\Models\HistoryModel;
use App\Models\PesananModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryModelFactory extends Factory
{
    protected $model = HistoryModel::class;

    public function definition(): array
    {
        $pesanan = PesananModel::inRandomOrder()->first();

        if (!$pesanan) {
            \Log::error('PesananModel not found.');
            throw new \Exception('PesananModel not found.');
        }

        return [
            'id_pesanan' => $pesanan->id_pesanan, // Pastikan id_pesanan adalah pesanan yang baru dibuat
            'status' => $this->faker->randomElement(['On_Progress', 'Finished', 'Cancelled']), // Status acak
        ];
    }
}
