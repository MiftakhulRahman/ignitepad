<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kolaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyek_id')->constrained('proyeks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('peran_kolaborator')->default('anggota'); // pembimbing, rekan, kontributor
            $table->boolean('bisa_edit')->default(false);
            $table->boolean('bisa_hapus')->default(false);
            
            $table->string('status_undangan')->default('diundang'); // diundang, diterima, ditolak
            
            $table->foreignId('diundang_oleh')->nullable()->constrained('users');
            $table->timestamp('direspon_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kolaborators');
    }
};