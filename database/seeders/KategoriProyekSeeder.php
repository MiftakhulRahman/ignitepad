<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriProyek;

class KategoriProyekSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['nama' => 'Web Development', 'slug' => 'web-development', 'warna' => '#3B82F6', 'ikon' => 'fa-globe'],
            ['nama' => 'Mobile Apps', 'slug' => 'mobile-apps', 'warna' => '#10B981', 'ikon' => 'fa-mobile'],
            ['nama' => 'AI & Machine Learning', 'slug' => 'ai-ml', 'warna' => '#8B5CF6', 'ikon' => 'fa-robot'],
            ['nama' => 'IoT & Hardware', 'slug' => 'iot', 'warna' => '#F59E0B', 'ikon' => 'fa-microchip'],
            ['nama' => 'Game Development', 'slug' => 'game-dev', 'warna' => '#EF4444', 'ikon' => 'fa-gamepad'],
            ['nama' => 'UI/UX Design', 'slug' => 'ui-ux', 'warna' => '#EC4899', 'ikon' => 'fa-pen-nib'],
        ];

        foreach ($kategoris as $k) {
            KategoriProyek::create($k);
        }
    }
}