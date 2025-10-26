<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Http\Controllers\Controller; // Pastikan ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Untuk membuat Slug
use Illuminate\Validation\Rule; // Untuk validasi unique

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua kategori, paginasi 10 per halaman
        $categories = Category::latest()->paginate(10);
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories',
            'slug' => 'nullable|string|max:100|unique:categories',
            'description' => 'nullable|string',
        ]);

        // Jika slug kosong, buat otomatis dari nama
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     * (Kita tidak pakai halaman 'show' individu, jadi bisa dilewati)
     */
    public function show(Category $category)
    {
         return redirect()->route('admin.categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories')->ignore($category->id), // Unik, kecuali untuk dirinya sendiri
            ],
            'slug' => [
                'nullable', 'string', 'max:100',
                Rule::unique('categories')->ignore($category->id), // Unik, kecuali untuk dirinya sendiri
            ],
            'description' => 'nullable|string',
        ]);

        // Jika slug kosong, buat otomatis dari nama
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Hati-hati: Nanti kita perlu cek apakah kategori ini dipakai proyek
        // Untuk sekarang, kita hapus langsung
        $category->delete();
        
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }
}