<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => $this->faker->unique()->regexify('[A-Z0-9]{20}'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'password_confirmation' => bcrypt('password'),
            'no_hp' => $this->faker->numerify('08##########'),
            'alamat' => $this->faker->address(),
            'deskripsi_alamat' => $this->faker->sentence(),
            'rating' => 0,
            'total_rating' => 0,
            'foto_diri' => base64_decode($this->faker->imageUrl(640, 480)),  // Mengambil URL gambar dari Faker dan mendekode base64 menjadi binary,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}