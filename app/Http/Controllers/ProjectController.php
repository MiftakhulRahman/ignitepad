<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Menampilkan halaman galeri semua proyek (yang sudah dipublikasi).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterCategory = $request->input('category');
        $filterTechnology = $request->input('technology');

        $query = Project::query()
                        ->where('status', 'published')
                        ->where('visibility', 'public')
                        ->with(['user', 'categories', 'technologies']);

        // --- BLOK PENCARIAN YANG DIMODIFIKASI ---
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Cari di Judul & Deskripsi Proyek
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                    
                  // Cari di Nama User (Pembuat)
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  })
                    
                  // Cari di Nama Kategori
                  ->orWhereHas('categories', function ($catQuery) use ($search) {
                      $catQuery->where('name', 'LIKE', "%{$search}%");
                  })

                  // Cari di Nama Teknologi
                  ->orWhereHas('technologies', function ($techQuery) use ($search) {
                      $techQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        // ----------------------------------------

        if ($filterCategory) {
            $query->whereHas('categories', function ($q) use ($filterCategory) {
                $q->where('slug', $filterCategory);
            });
        }

        if ($filterTechnology) {
            $query->whereHas('technologies', function ($q) use ($filterTechnology) {
                $q->where('slug', $filterTechnology);
            });
        }

        $projects = $query->latest()
                          ->paginate(12)
                          ->appends($request->query());

        // Menggunakan model yang di-*use* di awal *file*
        $categories = Category::orderBy('name')->get(); 
        $technologies = Technology::orderBy('name')->get();

        // Menggunakan 'project.index' sesuai permintaan
        return view('project.index', compact( 
            'projects',
            'search',
            'categories',
            'technologies',
            'filterCategory',
            'filterTechnology'
        ));
    }


    /**
     * Menampilkan halaman detail dari satu proyek.
     */
    public function show(Project $project)
    {
        // Cek Keamanan:
        // 1. Jika proyek tidak 'published' DAN
        // 2. Jika proyek visibility BUKAN 'public'
        if ($project->status !== 'published' || $project->visibility !== 'public') {

            // Kita harus cek apakah user login
            if (!auth()->check()) {
                abort(404); // Jika tamu, anggap tidak ada (404)
            }

            // Jika user login, cek apakah dia pemilik proyek
            if (auth()->id() !== $project->user_id) {

                // Jika bukan pemilik (dan proyek tidak publik), cek visibility 'university_only'
                if ($project->visibility === 'university_only') {
                    // Bolehkan, user sudah login
                } else {
                    abort(403); // Jika 'private', tolak (403 Forbidden)
                }
            }
            // Jika dia pemilik, biarkan dia lihat
        }

        // Load relasi yang dibutuhkan
        $project->load([
            'user.profile',
            'categories',
            'technologies',
            'images',
            'reviews.user',
            'collaborators' // <-- BARIS TAMBAHAN
        ]);

        // --- TAMBAHKAN LOGIKA LIKE & BOOKMARK ---
        $isLiked = false;
        $isBookmarked = false;

        if (auth()->check()) {
            $user = auth()->user();
            $isLiked = $user->likes()->where('project_id', $project->id)->exists();
            $isBookmarked = $user->bookmarks()->where('project_id', $project->id)->exists();
        }
        // ----------------------------------------

        // (Opsional) Hitung view count
        // $project->increment('view_count');

        // Kirim data ke view (Menggunakan 'project.show' sesuai permintaan Anda di akhir)
        return view('project.show', compact('project', 'isLiked', 'isBookmarked'));
    }
}