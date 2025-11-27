<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komentars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyek_id')->constrained('proyeks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('induk_id')->nullable()->constrained('komentars')->onDelete('cascade'); // Untuk reply
            
            $table->text('isi_html');
            $table->integer('jumlah_suka')->default(0);
            $table->integer('jumlah_balasan')->default(0);
            
            $table->timestamps();
            $table->softDeletes(); // Agar komentar dihapus tidak hilang permanen (opsional)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komentars');
    }
};