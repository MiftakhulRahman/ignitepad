<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PeranSeeder::class,
            ProgramStudiSeeder::class,
            KategoriProyekSeeder::class, 
            TeknologiSeeder::class,      
            PenggunaSeeder::class,
        ]);
    }
}