<?php

namespace Database\Factories;

use App\Models\RatingUserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingUserModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RatingUserModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id_user' => $this->faker->randomElement(\App\Models\User::pluck('id_user')->toArray()), // Ambil ID user yang ada
            'id_tukang' => $this->faker->randomElement(\App\Models\TukangModel::pluck('id_tukang')->toArray()), // Ambil ID tukang yang ada
            'rating' => $this->faker->numberBetween(1, 5),
            'ulasan' => $this->faker->sentence(),
            'tanggal_rating' => $this->faker->dateTimeThisYear(),
        ];
    }
}
