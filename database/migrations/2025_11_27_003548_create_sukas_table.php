<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sukas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Polymorphic: bisa like Proyek atau Komentar
            // Akan membuat kolom: likeable_type dan likeable_id
            $table->morphs('likeable'); 
            
            $table->timestamps();
            
            // Mencegah user like hal yang sama 2x
            $table->unique(['user_id', 'likeable_type', 'likeable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sukas');
    }
};