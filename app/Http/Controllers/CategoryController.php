<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan halaman detail untuk sebuah kategori,
     * beserta semua proyek di dalamnya.
     */
    public function show(Category $category)
    {
        // Load proyek-proyek yang terkait dengan kategori ini
        $projects = $category->projects()
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->with(['user', 'categories'])
            ->latest()
            ->paginate(12);
            
        return view('categories.show', compact('category', 'projects'));
    }
}