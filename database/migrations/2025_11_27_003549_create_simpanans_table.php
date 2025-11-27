<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simpanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('proyek_id')->constrained('proyeks')->onDelete('cascade');
            $table->string('folder')->default('default'); // default, inspirasi, dll
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'proyek_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpanans');
    }
};