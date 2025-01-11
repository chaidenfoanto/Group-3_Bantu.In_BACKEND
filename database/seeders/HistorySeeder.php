<?php

namespace Database\Seeders;

use App\Models\HistoryModel;
use App\Models\PesananModel;
use Illuminate\Database\Seeder;

class HistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Ambil semua id_pesanan dari tabel pesanan
        $pesanans = PesananModel::all();

        // Buat history untuk setiap pesanan
        foreach ($pesanans as $pesanan) {
            HistoryModel::create([
                'id_pesanan' => $pesanan->id_pesanan, // Mengaitkan history dengan id_pesanan yang ada
                'status' => 'not done', // Status awal 'not done'
            ]);
        }
    }
}
