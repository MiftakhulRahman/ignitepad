<?php

namespace App\Http\Controllers;

use App\Models\Technology;
use Illuminate\Http\Request;

class TechnologyController extends Controller
{
    /**
     * Menampilkan halaman detail untuk sebuah teknologi,
     * beserta semua proyek di dalamnya.
     */
    public function show(Technology $technology)
    {
        // Load proyek-proyek yang terkait dengan teknologi ini
        $projects = $technology->projects()
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->with(['user', 'categories'])
            ->latest()
            ->paginate(12);

        return view('technologies.show', compact('technology', 'projects'));
    }
}