<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use App\Models\Proyek;
use Illuminate\Http\Request;

class KomentarController extends Controller
{
    public function store(Request $request, $proyekId)
    {
        $request->validate([
            'isi' => 'required|string|max:1000',
        ]);

        $proyek = Proyek::findOrFail($proyekId);

        $komentar = $proyek->komentar()->create([
            'user_id' => auth()->id(),
            'isi' => $request->isi,
            'isi_html' => nl2br(e($request->isi)),
            'induk_id' => $request->induk_id, // Support Reply
        ]);
        
        // Jika ini balasan, increment jumlah_balasan di komentar induk
        if ($request->induk_id) {
            $induk = Komentar::find($request->induk_id);
            if ($induk) {
                $induk->increment('jumlah_balasan');
            }
        }
        
        // Increment jumlah komentar di proyek (opsional, jika ada kolomnya)
        // $proyek->increment('jumlah_komentar');

        return redirect()->back()->with('success', 'Komentar berhasil dikirim.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'isi' => 'required|string|max:1000',
        ]);

        $komentar = Komentar::findOrFail($id);

        if (auth()->id() !== $komentar->user_id) {
            abort(403);
        }

        $komentar->update([
            'isi' => $request->isi,
            'isi_html' => nl2br(e($request->isi)),
        ]);

        return redirect()->back()->with('success', 'Komentar diperbarui.');
    }

    public function destroy($id)
    {
        $komentar = Komentar::findOrFail($id);

        // Authorization: Hanya pemilik komentar atau admin yang bisa hapus
        if (auth()->id() !== $komentar->user_id && !auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $komentar->delete();

        return redirect()->back()->with('success', 'Komentar dihapus.');
    }
}
