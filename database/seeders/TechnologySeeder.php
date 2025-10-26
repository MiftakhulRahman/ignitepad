<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Technology; // <-- IMPORT MODEL

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technologies = [
            'Laravel',
            'React',
            'Vue.js',
            'Python',
            'Flutter',
            'TensorFlow',
            'Figma',
            'MySQL',
            'PHP',
            'JavaScript'
        ];

        foreach ($technologies as $techName) {
            Technology::firstOrCreate([ // Pakai firstOrCreate agar tidak duplikat
                'name' => $techName,
                'slug' => Str::slug($techName)
            ]);
        }
    }
}