<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramStudi;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = [
            [
                'nama' => 'Informatika',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'kode' => 'INF'
            ],
            [
                'nama' => 'Pendidikan Teknologi Informasi',
                'fakultas' => 'Fakultas Ilmu Pendidikan',
                'kode' => 'PTI'
            ],
        ];

        foreach ($prodis as $prodi) {
            ProgramStudi::create($prodi);
        }
    }
}