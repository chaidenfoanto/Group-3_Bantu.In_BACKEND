<?php

namespace Database\Factories;

use App\Models\HistoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryModelFactory extends Factory
{
    protected $model = HistoryModel::class;

    public function definition(): array
    {
        return [
            'id_pesanan' => \App\Models\PesananModel::factory(), // Pastikan id_pesanan adalah pesanan yang baru dibuat
            'status' => $this->faker->randomElement(['not done', 'done']), // Status acak
        ];
    }
}
