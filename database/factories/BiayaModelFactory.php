<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Biaya>
 */
class BiayaModelFactory extends Factory
{
    public function definition()
    {
        return [
            'biaya_servis' => 0, // Akan diubah secara manual di seeder
            'biaya_admin' => 1000,
            'biaya_total' => 0, // Akan diubah secara manual di seeder
            'jenis_servis' => '',
        ];
    }
}
