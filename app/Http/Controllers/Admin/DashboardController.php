<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;     // <-- 1. Import User
use App\Models\Project;  // <-- 2. Import Project

class DashboardController extends Controller
{
    public function index()
    {
        // 3. Ambil data statistik
        $stats = [
            'totalUsers' => User::count(),
            'totalProjects' => Project::count(),
            'projectsPendingReview' => Project::where('status', 'review')->count(),
            'projectsPublished' => Project::where('status', 'published')->count(),
        ];

        // 4. Ambil 5 user terbaru
        $recentUsers = User::latest()->take(5)->get();
        
        // 5. Ambil 5 proyek terbaru yang menunggu review
        $recentPendingProjects = Project::where('status', 'review')
                                        ->with('user')
                                        ->latest('updated_at')
                                        ->take(5)
                                        ->get();

        // 6. Kirim semua data ke view
        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentPendingProjects'));
    }
}