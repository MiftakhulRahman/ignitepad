<?php

namespace App\Http\Controllers;

use App\Models\Proyek;
use Illuminate\Http\Request;

class InteraksiController extends Controller
{
    public function toggleLike(Request $request, $id)
    {
        $proyek = Proyek::findOrFail($id);
        $user = auth()->user();

        if ($proyek->isLikedBy($user)) {
            // Unlike
            $proyek->likes()->where('user_id', $user->id)->delete();
            $proyek->decrement('jumlah_suka');
            $liked = false;
        } else {
            // Like
            $proyek->likes()->create(['user_id' => $user->id]);
            $proyek->increment('jumlah_suka');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $proyek->jumlah_suka,
        ]);
    }

    public function toggleSave(Request $request, $id)
    {
        $proyek = Proyek::findOrFail($id);
        $user = auth()->user();

        // Cek apakah sudah disimpan (Logic manual karena belum ada relasi isSavedBy di model Proyek yang saya lihat, 
        // tapi saya asumsikan ada tabel 'simpanans' atau sejenisnya berdasarkan app_summary.md)
        // Saya akan gunakan model Simpanan jika ada, atau relasi manual.
        // Berdasarkan file list tadi ada Simpanan.php
        
        $simpanan = \App\Models\Simpanan::where('user_id', $user->id)
            ->where('proyek_id', $proyek->id)
            ->first();

        if ($simpanan) {
            // Unsave
            $simpanan->delete();
            $proyek->decrement('jumlah_simpan');
            $saved = false;
        } else {
            // Save
            \App\Models\Simpanan::create([
                'user_id' => $user->id,
                'proyek_id' => $proyek->id,
            ]);
            $proyek->increment('jumlah_simpan');
            $saved = true;
        }

        return response()->json([
            'saved' => $saved,
            'count' => $proyek->jumlah_simpan,
        ]);
    }

    public function toggleLikeKomentar(Request $request, $id)
    {
        $komentar = \App\Models\Komentar::findOrFail($id);
        $user = auth()->user();

        // Cek manual like komentar (asumsi polymorphic 'Suka' bisa untuk 'App\Models\Komentar')
        // Atau buat tabel khusus. Berdasarkan app_summary.md, tabel 'Suka' polymorphic.
        
        $existingLike = $komentar->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $komentar->decrement('jumlah_suka');
            $liked = false;
        } else {
            $komentar->likes()->create(['user_id' => $user->id]);
            $komentar->increment('jumlah_suka');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $komentar->jumlah_suka,
        ]);
    }
}
