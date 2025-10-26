<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category; // <-- IMPORT MODEL

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Pengembangan Web',
            'Mobile Development',
            'Machine Learning',
            'Data Science',
            'Game Development',
            'UI/UX Design',
            'Jaringan & Keamanan Siber'
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate([ // Pakai firstOrCreate agar tidak duplikat
                'name' => $categoryName,
                'slug' => Str::slug($categoryName)
            ]);
        }
    }
}