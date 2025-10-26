<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectImage; // <-- Import model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectImageController extends Controller
{
    /**
     * Hapus satu gambar screenshot.
     */
    public function destroy(Request $request, ProjectImage $projectImage)
    {
        // 1. Otorisasi: Pastikan user yang login adalah pemilik proyek
        if ($projectImage->project->user_id !== Auth::id()) {
            abort(403, 'ANDA TIDAK BERHAK MENGHAPUS GAMBAR INI.');
        }

        // 2. Hapus file dari storage
        Storage::disk('public')->delete($projectImage->image_path);

        // 3. Hapus record dari database
        $projectImage->delete();

        // 4. Kembali
        return back()->with('success', 'Screenshot berhasil dihapus.');
    }
}