<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- 1. Import Auth
use App\Models\Project; // <-- 2. Import Project

class DashboardController extends Controller
{
    public function index()
    {
        // 3. Ambil user (Dosen) yang sedang login
        $lecturer = Auth::user();

        // 4. Ambil statistik
        $stats = [
            // Proyek yang sedang menunggu review (umum untuk semua dosen)
            'projectsPendingReview' => Project::where('status', 'review')->count(),
            
            // Proyek yang Dosen ini bimbing
            'supervisedProjectsCount' => Project::where('supervisor_id', $lecturer->id)->count(),
            
            // Total proyek yang sudah di-publish (info umum)
            'totalPublished' => Project::where('status', 'published')->count(),
        ];
        
        // 5. Ambil 5 proyek terbaru yang dibimbing
        $recentSupervisedProjects = Project::where('supervisor_id', $lecturer->id)
                                        ->with('user') // Ambil data mahasiswa
                                        ->latest()
                                        ->take(5)
                                        ->get();

        // 6. Ambil notifikasi yang belum dibaca
        $notifications = $lecturer->unreadNotifications;

        // 7. Kirim data ke view
        return view('lecturer.dashboard', compact('stats', 'recentSupervisedProjects', 'notifications'));
    }
}