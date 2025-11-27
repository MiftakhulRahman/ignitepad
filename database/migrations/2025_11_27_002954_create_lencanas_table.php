<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lencanas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('deskripsi');
            $table->string('ikon');
            $table->string('kategori'); // achievement, skill, participation
            $table->json('syarat_json')->nullable(); // Logic otomatisasi
            $table->timestamps();
        });

        // Pivot: User punya Lencana
        Schema::create('pengguna_lencana', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lencana_id')->constrained('lencanas')->onDelete('cascade');
            $table->timestamp('diperoleh_pada')->useCurrent();
            $table->primary(['user_id', 'lencana_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna_lencana');
        Schema::dropIfExists('lencanas');
    }
};