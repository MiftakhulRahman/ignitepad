<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Handle a bookmark/unbookmark toggle request.
     */
    public function toggle(Request $request, Project $project)
    {
        $user = $request->user();

        // Cari apakah user sudah bookmark proyek ini
        $bookmark = $user->bookmarks()->where('project_id', $project->id)->first();

        if ($bookmark) {
            // Jika sudah ada, hapus (unbookmark)
            $bookmark->delete();
            $bookmarked = false;
        } else {
            // Jika belum, buat (bookmark)
            $user->bookmarks()->create(['project_id' => $project->id]);
            $bookmarked = true;
        }

        // Kembalikan status baru
        return response()->json([
            'bookmarked' => $bookmarked,
        ]);
    }
}