<?php

namespace Database\Factories;

use App\Models\TukangModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

class TukangModelFactory extends Factory
{
    protected $model = TukangModel::class;

    public function definition(): array
    {
        return [
            'id_tukang' => $this->faker->unique()->regexify('[A-Z0-9]{20}'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'password_confirmation' => bcrypt('password'),
            'no_hp' => $this->faker->numerify('08##########'),
            'spesialisasi' => $this->faker->randomElement(['AC', 'LAS']),
            'ktp' => base64_decode($this->faker->imageUrl(640, 480)),  // Mengambil URL gambar dari Faker dan mendekode base64 menjadi binary
            'foto_diri' => base64_decode($this->faker->imageUrl(640, 480)),  // Mengambil URL gambar dari Faker dan mendekode base64 menjadi binary,
            'rating' => 0,
            'total_rating' => 0,
            'tukang_location' => json_encode([
                'lat' => $this->faker->latitude(-5.2, -5.1),
                'lng' => $this->faker->longitude(119.3, 119.4),
            ]),
            'remember_token' => Str::random(10),
        ];
    }

}