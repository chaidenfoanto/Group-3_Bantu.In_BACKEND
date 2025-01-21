<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fact;

class FactSeeder extends Seeder
{
    public function run()
    {
        Fact::create([
            'title' => 'Apa penyebab Freon pada AC habis?',
            'content' => 'Gas freon di dalam unit kondensor yang bocor adalah penyebab paling umum yang membuat stok freon AC berkurang dengan cepat. Selain pemasangannya yang salah, komponen AC yang menua dan aus juga bisa menjadi penyebab kebocoran gas.',
        ]);

        Fact::create([
            'title' => 'Mengapa AC Anda cepat kotor?',
            'content' => 'Penggunaan yang Berlebihan. Penggunaan yang berlebihan AC juga dapat menyebabkan AC menjadi kotor lebih cepat. Semakin lama dan sering Anda menggunakan AC, semakin banyak partikel debu dan kotoran yang akan masuk ke dalamnya. Solusi: Gunakan AC dengan bijak.',
        ]);
    }
}
