<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RatingUserModel;

class RatingUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\RatingUserModel::factory(10)->create(); // Menambahkan 10 data rating tukang
    }
}
