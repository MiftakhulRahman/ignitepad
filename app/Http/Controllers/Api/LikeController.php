<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Handle a like/unlike toggle request.
     */
    public function toggle(Request $request, Project $project)
    {
        $user = $request->user();

        // Cari apakah user sudah like proyek ini
        $like = $user->likes()->where('project_id', $project->id)->first();

        if ($like) {
            // Jika sudah ada, hapus (unlike)
            $like->delete();
            $liked = false;
        } else {
            // Jika belum, buat (like)
            $user->likes()->create(['project_id' => $project->id]);
            $liked = true;
        }

        // Hitung ulang total likes di proyek dan simpan
        $likeCount = $project->likes()->count();
        $project->like_count = $likeCount;
        $project->save();

        // Kembalikan status baru
        return response()->json([
            'liked' => $liked,
            'count' => $likeCount,
        ]);
    }
}