<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@unuha.ac.id'],
            [
                'name' => 'Admin IgnitePad',
                'nim' => 'admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        Profile::firstOrCreate(['user_id' => $adminUser->id]);


        // --- 2. TAMBAHKAN AKUN DOSEN ---
        $lecturerUser = User::firstOrCreate(
            ['email' => 'dosen@unuha.ac.id'], // Email login dosen
            [
                'name' => 'Budi Dosen, M.Kom.',
                'nim' => 'dosen123', // Dosen juga punya NIP/NIDN, kita pakai 'nim' dulu
                'password' => bcrypt('password'), // Password: "password"
                'role' => 'lecturer',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        Profile::firstOrCreate(['user_id' => $lecturerUser->id]);
        // --------------------------------


        // 3. Panggil Seeder Kategori dan Teknologi
        $this->call([
            CategorySeeder::class,
            TechnologySeeder::class,
        ]);
    }
}