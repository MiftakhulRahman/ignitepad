<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Peran;

class PeranSeeder extends Seeder
{
    public function run(): void
    {
        $perans = [
            [
                'nama' => 'superadmin',
                'slug' => 'superadmin',
                'deskripsi' => 'Administrator utama sistem'
            ],
            [
                'nama' => 'dosen',
                'slug' => 'dosen',
                'deskripsi' => 'Tenaga pengajar dan pembuat challenge'
            ],
            [
                'nama' => 'mahasiswa',
                'slug' => 'mahasiswa',
                'deskripsi' => 'Peserta didik dan pembuat proyek'
            ],
        ];

        foreach ($perans as $peran) {
            Peran::create($peran);
        }
    }
}