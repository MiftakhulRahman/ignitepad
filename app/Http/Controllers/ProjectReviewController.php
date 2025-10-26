<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectReviewController extends Controller
{
    /**
     * Menyimpan review/komentar baru.
     */
    public function store(Request $request, Project $project)
    {
        // 1. Validasi
        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:2000',
        ]);

        $user = Auth::user();

        // 2. Cek apakah user sudah pernah review?
        //    (Kita izinkan beberapa kali, atau batasi 1x)
        //    Untuk sekarang, kita izinkan berkali-kali.

        // 3. Simpan review
        $project->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'approved', // Langsung setujui (auto-approve)
        ]);
        
        // 4. (Opsional) Hitung ulang rating rata-rata proyek
        // $project->updateRating(); // (Ini fungsi custom jika diperlukan)

        // 5. Kembali ke halaman sebelumnya
        return back()->with('success', 'Komentar Anda berhasil diposting.');
    }
}