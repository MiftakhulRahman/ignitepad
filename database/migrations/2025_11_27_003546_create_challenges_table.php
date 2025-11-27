<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembuat_id')->constrained('users'); // Dosen pembuat
            $table->string('judul');
            $table->string('slug')->unique();
            
            // Konten
            $table->text('deskripsi');
            $table->longText('aturan_html')->nullable();
            $table->text('hadiah')->nullable();
            $table->string('banner')->nullable();
            
            // Settings
            $table->json('persyaratan')->nullable();
            $table->json('kategori_diizinkan')->nullable(); // ID kategori proyek yg boleh ikut
            
            // Jadwal
            $table->dateTime('tanggal_mulai');
            $table->dateTime('batas_waktu');
            $table->dateTime('tanggal_pengumuman')->nullable();
            
            // Status
            $table->string('status')->default('draft'); // draft, buka, tutup, selesai
            $table->integer('maks_peserta')->default(0); // 0 = unlimited
            
            // Counters
            $table->integer('jumlah_peserta')->default(0);
            $table->integer('jumlah_submisi')->default(0);
            
            // Pemenang (diisi nanti)
            $table->foreignId('pemenang_1_id')->nullable()->constrained('users');
            $table->foreignId('pemenang_2_id')->nullable()->constrained('users');
            $table->foreignId('pemenang_3_id')->nullable()->constrained('users');
            $table->timestamp('pemenang_diumumkan_pada')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};