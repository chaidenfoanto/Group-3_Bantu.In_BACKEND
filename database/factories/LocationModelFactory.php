<?php

namespace Database\Factories;

use App\Models\LocationModel;
use App\Models\User;
use App\Models\TukangModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationModelFactory extends Factory
{
    protected $model = LocationModel::class;

    public function definition(): array
    {
        return [
            'id_user' => $this->faker->randomElement(User::pluck('id_user')->toArray()), // Ambil id_user dari tabel users
            'is_started' => $this->faker->boolean(),
            'is_completed' => $this->faker->boolean(),
            'origin' => [
                'latitude' => $this->faker->latitude(-5, -10),
                'longitude' => $this->faker->longitude(118, 119),
            ],
            'destination' => [
                'latitude' => $this->faker->latitude(-90, 90),
                'longitude' => $this->faker->longitude(-180, 180),
            ],
            'destination_name' => $this->faker->address(),
        ];
    }
}
