<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TukangSeeder::class,
            RatingUserSeeder::class,
            RatingTukangSeeder::class,
            BiayaSeeder::class,
            PesananSeeder::class,
            DetailPesananSeeder::class,
            HistorySeeder::class,
            LocationSeeder::class,
            // PesananTukangSeeder::class,
            // PesananUserSeeder::class,
            // PembayaranSeeder::class,
            // PesananSeeder::class,
            // PesananSeeder::class,
            
            // PesananTukangSeeder::class,
            // PesananUserSeeder::class,
            // PembayaranSeeder::class,
        ]);
    }
}
