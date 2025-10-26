<?php

namespace App\Http\Controllers\Admin;

use App\Models\Technology; // <-- Model Teknologi
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TechnologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technologies = Technology::latest()->paginate(10); 
        
        return view('admin.technologies.index', compact('technologies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.technologies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:technologies',
            'slug' => 'nullable|string|max:100|unique:technologies',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Technology::create($validated);

        return redirect()->route('admin.technologies.index')
                         ->with('success', 'Teknologi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Technology $technology)
    {
        // Kita tidak pakai halaman show, lempar ke index
        return redirect()->route('admin.technologies.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technology $technology)
    {
        return view('admin.technologies.edit', compact('technology'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Technology $technology)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('technologies')->ignore($technology->id),
            ],
            'slug' => [
                'nullable', 'string', 'max:100',
                Rule::unique('technologies')->ignore($technology->id),
            ],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $technology->update($validated);

        return redirect()->route('admin.technologies.index')
                         ->with('success', 'Teknologi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technology $technology)
    {
        $technology->delete();
        
        return redirect()->route('admin.technologies.index')
                         ->with('success', 'Teknologi berhasil dihapus.');
    }
}