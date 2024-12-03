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
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->integer('id_detailpesanan')->primary()->unsigned()->autoIncrement();
            $table->unsignedInteger('id_pesanan');
            $table->string('nama_layanan');
            $table->decimal('harga_layanan', 10, 2);
            $table->integer('kuantitas');
            $table->decimal('subtotal', 10, 2);
            $table->text('deskripsi_servis');
            $table->timestamps();

            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
