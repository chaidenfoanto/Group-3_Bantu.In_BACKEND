<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menambah data ke tabel users
        DB::table('users')->insert([
            'id_user' => 'user001',
            'name' => 'cihuyyy',
            'email' => 'johndoe@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'), // Gunakan Hash untuk enkripsi password
            'password_confirmation' => 'password123',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Kebon Jeruk No. 10, Jakarta',
            'deskripsi_alamat' => 'Dekat dengan restoran dan pusat perbelanjaan',
            'rating' => 4.5,
            'total_rating' => 100,
            'foto_diri' => null, // Foto bisa ditambahkan sesuai kebutuhan
            'remember_token' => Str::random(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::getPdo()->lastInsertId();
    }
}
