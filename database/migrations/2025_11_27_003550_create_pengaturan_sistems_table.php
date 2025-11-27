<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaturan_sistems', function (Blueprint $table) {
            $table->string('kunci')->primary(); // key
            $table->text('nilai')->nullable();
            $table->string('tipe_data')->default('string'); // string, boolean, json
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistems');
    }
};
