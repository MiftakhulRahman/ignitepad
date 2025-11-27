<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('fakultas');
            $table->string('kode')->unique(); // Misal: IF, SI
            $table->timestamps();
        });

        // Kita tambahkan kolom prodi_id ke tabel users sekarang
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->after('nidn')->constrained('program_studis')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Hapus foreign key dulu sebelum drop tabel
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn('prodi_id');
        });
        
        Schema::dropIfExists('program_studis');
    }
};