<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    /**
     * Menampilkan daftar proyek yang di-bookmark oleh user.
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil ID proyek yang di-bookmark user
        $bookmarkedProjectIds = $user->bookmarks()->pluck('project_id');

        // Ambil data proyek tersebut (yang masih published & public)
        $bookmarkedProjects = \App\Models\Project::whereIn('id', $bookmarkedProjectIds)
                                    ->where('status', 'published') // Hanya tampilkan yg masih published
                                    ->where('visibility', 'public') // Hanya tampilkan yg masih public
                                    ->with(['user', 'categories']) // Eager load
                                    ->latest('id') // Urutkan berdasarkan kapan di-bookmark (kurang lebih)
                                    ->paginate(9);

        return view('bookmarks.index', compact('bookmarkedProjects'));
    }
}