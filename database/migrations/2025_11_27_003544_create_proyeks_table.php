<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyeks', function (Blueprint $table) {
            $table->id();
            // Identitas & Konten
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kategori_id')->constrained('kategori_proyeks');
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('deskripsi')->nullable(); // Deskripsi singkat
            $table->longText('konten_html')->nullable(); // Konten lengkap
            $table->string('thumbnail')->nullable();
            $table->json('galeri_gambar')->nullable(); // Array path gambar
            
            // Links
            $table->string('url_demo')->nullable();
            $table->string('url_repository')->nullable();
            $table->string('url_video')->nullable();
            
            // Meta & Status
            $table->json('tag')->nullable();
            $table->string('status')->default('draft'); // draft, terbit, arsip
            $table->string('visibilitas')->default('publik'); // publik, terbatas, privat
            $table->boolean('boleh_komentar')->default(true);
            $table->boolean('unggulan')->default(false); // Featured
            
            // Counters (untuk performa query)
            $table->integer('jumlah_lihat')->default(0);
            $table->integer('jumlah_suka')->default(0);
            $table->integer('jumlah_simpan')->default(0);
            $table->integer('jumlah_komentar')->default(0);
            
            $table->timestamp('terbit_pada')->nullable();
            $table->timestamps();
        });

        // Tabel Pivot: Proyek punya banyak Teknologi
        Schema::create('proyek_teknologi', function (Blueprint $table) {
            $table->foreignId('proyek_id')->constrained('proyeks')->onDelete('cascade');
            $table->foreignId('teknologi_id')->constrained('teknologis')->onDelete('cascade');
            $table->primary(['proyek_id', 'teknologi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyek_teknologi');
        Schema::dropIfExists('proyeks');
    }
};