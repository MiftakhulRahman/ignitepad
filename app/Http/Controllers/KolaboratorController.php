<?php

namespace App\Http\Controllers;

use App\Models\Kolaborator;
use App\Models\Proyek;
use Illuminate\Http\Request;

class KolaboratorController extends Controller
{
    // Tambah Kolaborator (Kirim Undangan)
    public function store(Request $request, $proyekId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'peran_kolaborator' => 'required|in:pembimbing,rekan,kontributor,anggota',
            'bisa_edit' => 'boolean',
            'bisa_hapus' => 'boolean',
        ]);

        $proyek = Proyek::findOrFail($proyekId);

        // Authorization: Only owner or admin can add collaborators
        if (auth()->id() !== $proyek->user_id && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk menambah kolaborator.');
        }

        // Check if user is already a collaborator
        $exists = Kolaborator::where('proyek_id', $proyek->id)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Pengguna sudah menjadi kolaborator proyek ini.');
        }

        // Create collaborator invitation
        Kolaborator::create([
            'proyek_id' => $proyek->id,
            'user_id' => $validated['user_id'],
            'peran_kolaborator' => $validated['peran_kolaborator'],
            'bisa_edit' => $validated['bisa_edit'] ?? false,
            'bisa_hapus' => $validated['bisa_hapus'] ?? false,
            'status_undangan' => 'diterima', // Auto accept for now
            'diundang_oleh' => auth()->id(),
        ]);

        return back()->with('success', 'Kolaborator berhasil ditambahkan.');
    }

    // Hapus Kolaborator
    public function destroy($proyekId, $kolaboratorId)
    {
        $proyek = Proyek::findOrFail($proyekId);
        $kolaborator = Kolaborator::findOrFail($kolaboratorId);

        // Authorization: Only owner or admin can remove collaborators
        if (auth()->id() !== $proyek->user_id && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus kolaborator.');
        }

        // Ensure the collaborator belongs to this project
        if ($kolaborator->proyek_id !== $proyek->id) {
            abort(404, 'Kolaborator tidak ditemukan.');
        }

        $kolaborator->delete();

        return back()->with('success', 'Kolaborator berhasil dihapus.');
    }
}
