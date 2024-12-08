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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->string('id_pesanan')->primary();
            $table->string('id_user', 20);
            $table->string('id_tukang', 20);
            $table->unsignedInteger('id_biaya');
            $table->datetime('waktu_pesan'); 
            $table->datetime('waktu_servis');
            $table->text('alamat_servis');
            $table->enum('metode_pembayaran', ['Tunai', 'Non-tunai'])->default('Tunai');
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users');
            
            $table->foreign('id_tukang')
                ->references('id_tukang')
                ->on('tukang');

            $table->foreign('id_biaya')
                ->references('id_biaya')
                ->on('biaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
