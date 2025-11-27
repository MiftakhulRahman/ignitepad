<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perans', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // superadmin, dosen, mahasiswa
            $table->string('slug')->unique();
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        // Tabel Pivot untuk User <-> Peran (Many to Many)
        Schema::create('pengguna_peran', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('peran_id')->constrained('perans')->onDelete('cascade');
            $table->primary(['user_id', 'peran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna_peran');
        Schema::dropIfExists('perans');
    }
};