<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project; // <-- 1. IMPORT PROJECT

class HomeController extends Controller
{
    /**
     * Menampilkan Halaman Homepage Publik
     */
    public function index()
    {
        // 2. AMBIL 6 PROYEK TERBARU YANG PUBLISHED
        $latestProjects = Project::where('status', 'published')
                                ->where('visibility', 'public')
                                ->with(['user', 'categories']) // Eager load relasi
                                ->latest() // Urutkan dari terbaru
                                ->take(6)
                                ->get();

        // 3. KIRIM DATA KE VIEW
        return view('home', compact('latestProjects'));
    }

    /**
     * Mengarahkan user ke dashboard yang sesuai
     * berdasarkan role mereka setelah login.
     */
    public function dashboardRedirect()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = Auth::user()->role;

        if ($role == 'admin') {
            return redirect()->route('admin.dashboard');
        } 
        
        if ($role == 'student') {
            return redirect()->route('student.dashboard');
        } 
        
        if ($role == 'lecturer') {
            return redirect()->route('lecturer.dashboard');
        }

        // Default fallback jika role tidak dikenal
        Auth::logout();
        return redirect()->route('home')->with('error', 'Role tidak valid.');
    }
}