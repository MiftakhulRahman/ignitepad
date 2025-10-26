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
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom 'name' bawaan breeze jika ingin ganti struktur
            // $table->dropColumn('name'); 

            // Tambahkan kolom kita
            $table->string('nim', 20)->unique()->nullable()->after('id');
            $table->enum('role', ['student', 'lecturer', 'admin'])->default('student')->after('password');
            $table->string('photo')->nullable()->after('role');
            $table->string('phone', 20)->nullable()->after('photo');
            $table->integer('batch_year')->nullable()->after('phone');
            $table->enum('status', ['active', 'graduated', 'inactive'])->default('active')->after('batch_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nim',
                'role',
                'photo',
                'phone',
                'batch_year',
                'status'
            ]);
        });
    }
};
