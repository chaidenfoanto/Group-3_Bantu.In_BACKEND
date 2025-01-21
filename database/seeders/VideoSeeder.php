<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Video;

class VideoSeeder extends Seeder
{
    public function run()
    {
        Video::create([
            'title' => 'Penyebab AC Cepat Rusak dan Boros Listrik | Tips AC Lebih Awet dan Hemat Listrik',
            'embed_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/QGi7y-E6e0k?si=vsvoctPdxLvD4f7o" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
            'description' => 'Ac Rumah.\n1. Hindari penggunaan 24 jam\n2. Jangan menggubah-ubah temperatur suhu(rec 22-27) pilih salah satu\n3. Selesai pake ac tidak perlu cabut colokan power. (Kecuali bepergian rumah dengan waktu lama)\n4. Rutin membersihkan filter jaring yg di indoor(ac yg diruangan) setidaknya 1 kali sebulan.',
        ]);

        Video::create([
            'title' => 'Cara Menemukan Teknisi AC yang Jujur',
            'embed_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/tBayqwa6zU0?si=BkdtiNWO9neTTnIS" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
            'description' => 'Tips dan trik memilih teknisi AC terpercaya.',
        ]);
    }
}
