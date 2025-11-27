<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teknologi;

class TeknologiSeeder extends Seeder
{
    public function run(): void
    {
        $teks = [
            // FRAMEWORKS
            [
                'nama' => 'Laravel',
                'slug' => 'laravel',
                'kategori_teknologi' => 'Framework',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg',
                'warna' => '#FF2D20'
            ],
            [
                'nama' => 'Vue.js',
                'slug' => 'vuejs',
                'kategori_teknologi' => 'Framework',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg',
                'warna' => '#4FC08D'
            ],
            [
                'nama' => 'React',
                'slug' => 'react',
                'kategori_teknologi' => 'Framework',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg',
                'warna' => '#61DAFB'
            ],
            [
                'nama' => 'Tailwind CSS',
                'slug' => 'tailwindcss',
                'kategori_teknologi' => 'Framework',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-plain.svg',
                'warna' => '#06B6D4'
            ],
            [
                'nama' => 'Django',
                'slug' => 'django',
                'kategori_teknologi' => 'Framework',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/django/django-plain.svg',
                'warna' => '#092E20'
            ],
            [
                'nama' => 'Flutter',
                'slug' => 'flutter',
                'kategori_teknologi' => 'Framework',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/flutter/flutter-original.svg',
                'warna' => '#02569B'
            ],

            // LANGUAGES
            [
                'nama' => 'PHP',
                'slug' => 'php',
                'kategori_teknologi' => 'Language',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg',
                'warna' => '#777BB4'
            ],
            [
                'nama' => 'Python',
                'slug' => 'python',
                'kategori_teknologi' => 'Language',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg',
                'warna' => '#3776AB'
            ],
            [
                'nama' => 'JavaScript',
                'slug' => 'javascript',
                'kategori_teknologi' => 'Language',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg',
                'warna' => '#F7DF1E'
            ],

            // TOOLS & DB
            [
                'nama' => 'MySQL',
                'slug' => 'mysql',
                'kategori_teknologi' => 'Database',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg',
                'warna' => '#4479A1'
            ],
            [
                'nama' => 'Git',
                'slug' => 'git',
                'kategori_teknologi' => 'Tool',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg',
                'warna' => '#F05032'
            ],
             [
                'nama' => 'Figma',
                'slug' => 'figma',
                'kategori_teknologi' => 'Tool',
                'ikon_url' => 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/figma/figma-original.svg',
                'warna' => '#F24E1E'
            ],
        ];

        foreach ($teks as $t) {
            Teknologi::create($t);
        }
    }
}