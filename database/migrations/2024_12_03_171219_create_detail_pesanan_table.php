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
            $table->string('id_pesanan');
            $table->string('nama_layanan');
            $table->decimal('harga_layanan', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('deskripsi_servis')->nullable();
            $table->timestamps();

            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->onDelete('cascade');
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
