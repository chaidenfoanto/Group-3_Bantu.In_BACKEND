<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tukang', function (Blueprint $table) {
            $table->string('id_tukang', 20)->primary();
            $table->string('name', 50);
            $table->string('email', 50)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_confirmation')->nullable(); // Mengizinkan null
            // $table->string('no_hp', 15);
            // $table->enum('spesialisasi', ['AC', 'LAS']);
            // $table->binary('ktp')->nullable(); 
            // $table->float('rating', 2, 1);
            // $table->float('total_rating', 4, 1);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tukang');
    }
};