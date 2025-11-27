<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Peran;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID Peran
        $roleAdmin = Peran::where('slug', 'superadmin')->first();
        $roleDosen = Peran::where('slug', 'dosen')->first();
        $roleMhs = Peran::where('slug', 'mahasiswa')->first();

        // 1. Buat SUPERADMIN
        $admin = User::create([
            'nama' => 'Super Admin',
            'username' => 'admin',
            'email' => 'admin@ignitepad.com',
            'password' => Hash::make('password'), // Password: password
            'registrasi_selesai' => true,
            'email_verified_at' => now(),
        ]);
        $admin->perans()->attach($roleAdmin->id);

        // 2. Buat DOSEN (Prodi PTI)
        $dosen = User::create([
            'nama' => 'Bapak Dosen PTI',
            'username' => 'dosenpti',
            'email' => 'dosen@ignitepad.com',
            'password' => Hash::make('password'),
            'nidn' => '1234567890',
            'prodi_id' => 2, // ID 2 = PTI (sesuai urutan seeder prodi)
            'registrasi_selesai' => true,
            'email_verified_at' => now(),
        ]);
        $dosen->perans()->attach($roleDosen->id);

        // 3. Buat MAHASISWA (Prodi Informatika)
        $mhs = User::create([
            'nama' => 'Mahasiswa Keren',
            'username' => 'mahasiswa',
            'email' => 'mhs@ignitepad.com',
            'password' => Hash::make('password'),
            'nim' => '20250001',
            'prodi_id' => 1, // ID 1 = Informatika
            'registrasi_selesai' => true,
            'email_verified_at' => now(),
        ]);
        $mhs->perans()->attach($roleMhs->id);
    }
}