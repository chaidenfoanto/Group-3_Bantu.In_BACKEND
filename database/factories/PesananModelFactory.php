<?php

namespace Database\Factories;

use App\Models\PesananModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesananModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PesananModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id_pesanan' => $this->faker->unique()->regexify('[A-Z0-9]{20}'),
            'id_user' => $this->faker->randomElement(\App\Models\User::pluck('id_user')->toArray()), // Ambil ID user dari tabel users
            'id_tukang' => $this->faker->randomElement(\App\Models\TukangModel::pluck('id_tukang')->toArray()), // Ambil ID tukang dari tabel tukang
            'id_biaya' => $this->faker->randomElement(\App\Models\BiayaModel::pluck('id_biaya')->toArray()), // Ambil ID biaya dari tabel biaya
            'waktu_pesan' => now(), // Waktu pesan adalah sekarang
            'waktu_servis' => $this->faker->dateTimeBetween('+1 day', '+7 days'), // Waktu servis dalam rentang 1-7 hari ke depan
            'alamat_servis' => $this->faker->address, // Alamat servis acak
            'metode_pembayaran' => $this->faker->randomElement(['Cash', 'Non-cash']), // Pilih metode pembayaran acak
        ];
    }
}
