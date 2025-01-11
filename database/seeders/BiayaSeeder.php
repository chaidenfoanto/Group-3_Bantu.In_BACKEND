<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BiayaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'biaya_servis' => 75000,
                'biaya_admin' => 1000,
                'biaya_total' => 75000 + 1000,
                'jenis_servis' => 'Cuci AC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_servis' => 400000,
                'biaya_admin' => 1000,
                'biaya_total' => 400000 + 1000,
                'jenis_servis' => 'Isi Freon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_servis' => 55000,
                'biaya_admin' => 1000,
                'biaya_total' => 55000 + 1000,
                'jenis_servis' => 'Servis AC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('biaya')->insert($data);
    }
}
