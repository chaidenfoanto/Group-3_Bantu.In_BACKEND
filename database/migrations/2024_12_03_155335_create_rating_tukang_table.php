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
        Schema::create('rating_tukang', function (Blueprint $table) {
            $table->integer('id_ratingtukang')->primary()->unsigned()->autoIncrement();
            $table->string('id_user', 20);
            $table->string('id_tukang', 20);
            $table->integer('rating');
            $table->text('ulasan');
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users');

            $table->foreign('id_tukang')
                ->references('id_tukang')
                ->on('tukang'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_tukang');
    }
};
