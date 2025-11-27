<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Identitas
            $table->string('nama');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            
            // Profile
            $table->string('avatar')->nullable();
            $table->string('sampul')->nullable();
            $table->text('bio')->nullable();
            
            // Akademik
            $table->string('nim')->nullable()->unique(); // Untuk Mahasiswa
            $table->string('nidn')->nullable()->unique(); // Untuk Dosen
            // Note: prodi_id relasi akan kita tambahkan nanti setelah tabel prodi jadi
            
            // Sosial
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();
            
            // Status
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('registrasi_selesai')->default(false);
            $table->boolean('status_aktif')->default(true);
            
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};