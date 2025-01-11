<?php
namespace Database\Factories;

use App\Models\DetailPesananModel;
use App\Models\PesananModel;
use App\Models\BiayaModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPesananModelFactory extends Factory
{
    protected $model = DetailPesananModel::class;

    public function definition(): array
    {
        // Ambil pesanan acak yang valid
        $pesanan = PesananModel::inRandomOrder()->first();

        if (!$pesanan) {
            \Log::error('PesananModel not found.');
            throw new \Exception('PesananModel not found.');
        }

        // Ambil biaya yang terkait dengan pesanan
        $biaya = BiayaModel::find($pesanan->id_biaya);

        if (!$biaya) {
            \Log::error('BiayaModel not found for id: ' . $pesanan->id_biaya);
            throw new \Exception('BiayaModel not found for id: ' . $pesanan->id_biaya);
        }

        $kuantitas = $this->faker->numberBetween(1, 5);
        $subtotal = $biaya->biaya_servis * $kuantitas;

        return [
            'id_pesanan' => $pesanan->id_pesanan,
            'nama_layanan' => $biaya->jenis_servis,
            'harga_layanan' => $biaya->biaya_servis,
            'kuantitas' => $kuantitas,
            'subtotal' => $subtotal,
            'deskripsi_servis' => $this->faker->sentence(),
        ];
    }
}
