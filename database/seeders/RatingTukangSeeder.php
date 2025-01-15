<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RatingTukangModel;

class RatingTukangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\RatingTukangModel::factory(10)->create(); // Menambahkan 10 data rating tukang
    }
}
