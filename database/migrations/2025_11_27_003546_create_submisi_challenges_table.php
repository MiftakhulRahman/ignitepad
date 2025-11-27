<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submisi_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('proyek_id')->nullable()->constrained('proyeks');
            
            $table->string('status')->default('bergabung'); // bergabung, terkirim, dinilai
            $table->text('catatan_peserta')->nullable();
            
            // Hasil Penilaian
            $table->decimal('nilai_total', 5, 2)->nullable(); // Misal 95.50
            $table->string('grade')->nullable(); // A, B, C
            $table->integer('peringkat')->nullable();
            $table->longText('umpan_balik_html')->nullable();
            
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamp('dinilai_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submisi_challenges');
    }
};