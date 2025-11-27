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
        Schema::create('papan_peringkats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('poin_total')->default(0);
            $table->integer('poin_proyek')->default(0);
            $table->integer('poin_interaksi')->default(0); // Suka/Komen
            $table->integer('poin_challenge')->default(0);

            $table->integer('peringkat_global')->nullable();
            $table->integer('peringkat_prodi')->nullable();

            $table->timestamp('terakhir_dihitung_pada')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papan_peringkats');
    }
};
