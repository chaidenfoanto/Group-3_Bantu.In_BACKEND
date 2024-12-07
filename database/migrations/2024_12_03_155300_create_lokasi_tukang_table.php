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
        Schema::create('lokasi_tukang', function (Blueprint $table) {
            $table->integer('id_lokasi')->primary()->unsigned()->autoIncrement();
            $table->string('id_tukang', 20);
            $table->float('latitude'); // 10 digit total, 6 digit untuk desimal
            $table->float('longitude'); // 10 digit total, 6 digit untuk desimal
            $table->timestamps();

            $table->foreign('id_tukang')
                ->references('id_tukang')
                ->on('tukang') // Tabel penggunas adalah tabel librarian
                ->onDelete('cascade'); // Jika pengguna dihapus, maka lokasi akan terhapus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_tukang');
    }
};
