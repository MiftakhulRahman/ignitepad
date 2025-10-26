<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- 1. Import Auth

class DashboardController extends Controller
{
    public function index()
    {
        // 2. Ambil user yang sedang login
        $user = Auth::user();

        // 3. Ambil statistik proyek milik user ini
        $stats = [
            'totalProjects' => $user->projects()->count(),
            'totalLikes'    => $user->projects()->sum('like_count'),
            'totalViews'    => $user->projects()->sum('view_count'), // (Nanti bisa kita isi)
            'published'     => $user->projects()->where('status', 'published')->count(),
            'pendingReview' => $user->projects()->where('status', 'review')->count(),
        ];

        // 4. Ambil 5 proyek terbaru
        $recentProjects = $user->projects()->latest()->take(5)->get();

        // 5. Kirim data ke view
        return view('student.dashboard', compact('stats', 'recentProjects'));
    }
}