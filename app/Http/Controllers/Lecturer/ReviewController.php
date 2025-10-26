<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Notifications\ProjectStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ReviewController extends Controller
{
    /**
     * Menampilkan daftar proyek yang statusnya 'review'.
     */
    public function index()
    {
        $projectsToReview = Project::where('status', 'review')
            ->with('user') // Eager load data mahasiswa
            ->latest('updated_at')
            ->paginate(10);

        return view('lecturer.reviews.index', compact('projectsToReview'));
    }

    /**
     * Menampilkan detail proyek yang akan di-review.
     */
    public function show(Project $project)
    {
        // Hanya izinkan lihat jika statusnya 'review'
        if ($project->status !== 'review') {
            return redirect()->route('lecturer.reviews.index')
                ->with('error', 'Proyek ini sudah tidak perlu di-review.');
        }

        // Load semua relasi yang dibutuhkan
        $project->load(['user', 'supervisor', 'categories', 'technologies']);

        return view('lecturer.reviews.show', compact('project'));
    }

    /**
     * Memproses aksi (Approve / Reject).
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject'],
        ]);

        $newStatus = ''; // Variabel untuk menyimpan status baru

        if ($validated['action'] == 'approve') {
            $project->status = 'published';
            $newStatus = 'published'; // <-- Simpan status baru
            $message = 'Proyek berhasil disetujui dan dipublikasi.';
        } else { // 'reject'
            $project->status = 'draft';
            $newStatus = 'draft'; // <-- Simpan status baru
            $message = 'Proyek berhasil ditolak dan dikembalikan ke draft.';
        }

        $project->save();

        // --- 3. KIRIM NOTIFIKASI KE PEMBUAT PROYEK ---
        $project->user->notify(new ProjectStatusUpdated($project, $newStatus));
        // Atau bisa juga:
        // Notification::send($project->user, new ProjectStatusUpdated($project, $newStatus));
        // -----------------------------------------

        return redirect()->route('lecturer.reviews.index')
            ->with('success', $message);
    }
}
