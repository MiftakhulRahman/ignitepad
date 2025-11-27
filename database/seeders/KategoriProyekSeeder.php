<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriProyek;

class KategoriProyekSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['nama' => 'Web Development', 'slug' => 'web-development', 'warna' => '#3B82F6', 'ikon' => 'Globe', 'urutan' => 1],
            ['nama' => 'Mobile Apps', 'slug' => 'mobile-apps', 'warna' => '#10B981', 'ikon' => 'Smartphone', 'urutan' => 2],
            ['nama' => 'AI & Machine Learning', 'slug' => 'ai-ml', 'warna' => '#8B5CF6', 'ikon' => 'Cpu', 'urutan' => 3],
            ['nama' => 'IoT & Hardware', 'slug' => 'iot', 'warna' => '#F59E0B', 'ikon' => 'HardDrive', 'urutan' => 4],
            ['nama' => 'Game Development', 'slug' => 'game-dev', 'warna' => '#EF4444', 'ikon' => 'Gamepad2', 'urutan' => 5],
            ['nama' => 'UI/UX Design', 'slug' => 'ui-ux', 'warna' => '#EC4899', 'ikon' => 'PenTool', 'urutan' => 6],
        ];

        foreach ($kategoris as $k) {
            KategoriProyek::create($k);
        }
    }
}
