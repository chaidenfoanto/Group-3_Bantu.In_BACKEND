<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\TukangModel;
use Illuminate\Database\Seeder;

class TukangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TukangModel::factory()->count(10)->create();
    }
}