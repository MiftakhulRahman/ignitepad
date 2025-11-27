<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_kriterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submisi_id')->constrained('submisi_challenges')->onDelete('cascade');
            $table->foreignId('kriteria_id')->constrained('kriteria_penilaians')->onDelete('cascade');
            $table->decimal('nilai', 5, 2); // 0-100
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_kriterias');
    }
};