<?php

namespace Database\Seeders;

use App\Models\LocationModel;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat 50 lokasi menggunakan factory
        LocationModel::factory(7)->create();
    }
}
