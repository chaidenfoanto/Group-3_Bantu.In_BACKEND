<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\TukangModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('location')) {
            Schema::create('location', function (Blueprint $table) {
                $table->integer('id_lokasi')->primary()->unsigned()->autoIncrement();
                $table->string('id_user', 20);
                $table->string('id_tukang', 20)->nullable();
                $table->boolean('is_started')->default(false);
                $table->boolean('is_completed')->default(false);
                $table->json('origin')->nullable();
                $table->json('destination')->nullable();
                $table->string('destination_name')->nullable();
                $table->timestamps();

                $table->foreign('id_user')
                    ->references('id_user')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('id_tukang')
                    ->references('id_tukang')
                    ->on('tukang')
                    ->onDelete('cascade'); // Tambahkan ini
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_location');
    }
};
