<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DetailPesananModel;
use App\Models\PesananModel;

class DetailPesananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua id_pesanan yang valid dari tabel pesanan
        $pesananIds = PesananModel::pluck('id_pesanan')->toArray();

        // Cek apakah ada data id_pesanan
        if (empty($pesananIds)) {
            \Log::error('Tidak ada data id_pesanan ditemukan di tabel pesanan.');
            throw new \Exception('Tidak ada data id_pesanan ditemukan di tabel pesanan.');
        }

        // Buat 10 data dummy untuk detail pesanan dengan id_pesanan yang valid
        foreach ($pesananIds as $idPesanan) {
            DetailPesananModel::factory()->create([
                'id_pesanan' => $idPesanan,
            ]);
        }
    }
}
