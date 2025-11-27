<?php

namespace App\Http\Controllers;

use App\Models\SubmisiChallenge;
use App\Models\Challenge;
use App\Models\Proyek;
use App\Models\NilaiKriteria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SubmisiController extends Controller
{
    // --- MAHASISWA: JOIN & SUBMIT ---

    public function join(Request $request, Challenge $challenge)
    {
        // Cek apakah sudah join
        $exists = SubmisiChallenge::where('challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Anda sudah bergabung di challenge ini.');
        }

        SubmisiChallenge::create([
            'challenge_id' => $challenge->id,
            'user_id' => auth()->id(),
            'status' => 'bergabung',
        ]);

        $challenge->increment('jumlah_peserta');

        return redirect()->back()->with('success', 'Berhasil bergabung! Silakan submit proyek Anda sebelum deadline.');
    }

    public function submit(Request $request, Challenge $challenge)
    {
        $request->validate([
            'proyek_id' => 'required|exists:proyeks,id',
            'catatan' => 'nullable|string|max:500',
        ]);

        $submisi = SubmisiChallenge::where('challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Validasi Kepemilikan Proyek
        $proyek = Proyek::findOrFail($request->proyek_id);
        if ($proyek->user_id !== auth()->id()) {
            abort(403, 'Ini bukan proyek Anda.');
        }

        $submisi->update([
            'proyek_id' => $request->proyek_id,
            'catatan_peserta' => $request->catatan,
            'status' => 'terkirim',
            'dikirim_pada' => now(),
        ]);

        $challenge->increment('jumlah_submisi');

        return redirect()->back()->with('success', 'Proyek berhasil disubmit! Semoga sukses.');
    }

    // --- DOSEN: DASHBOARD PENILAIAN ---

    public function kelola(Challenge $challenge)
    {
        // Security Check
        if (auth()->id() !== $challenge->pembuat_id && !auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $challenge->load('kriteriaPenilaian');

        // Ambil semua submisi + Data Proyek + Nilai yang sudah ada
        $submisis = SubmisiChallenge::where('challenge_id', $challenge->id)
            ->with(['user', 'proyek.kategori', 'nilaiKriteria'])
            ->latest('dikirim_pada')
            ->get()
            ->transform(function ($sub) {
                // Format nilai agar mudah dibaca frontend (Map kriteria_id => nilai)
                $nilaiMap = []; // Fixed: {} is not valid PHP array syntax, used []
                foreach ($sub->nilaiKriteria as $nk) {
                    $nilaiMap[$nk->kriteria_id] = $nk->nilai;
                }
                $sub->nilai_map = $nilaiMap;
                return $sub;
            });

        return Inertia::render('Challenge/Kelola', [
            'challenge' => $challenge,
            'submisis' => $submisis,
        ]);
    }

    // --- DOSEN: SIMPAN NILAI ---

    public function nilai(Request $request, $id)
    {
        $submisi = SubmisiChallenge::findOrFail($id);
        $challenge = $submisi->challenge;

        // Security Check
        if (auth()->id() !== $challenge->pembuat_id && !auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $request->validate([
            'nilai' => 'required|array', // [kriteria_id => score]
            'umpan_balik' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $submisi, $challenge) {
            $totalScore = 0;
            $totalBobot = 0;

            // 1. Simpan Nilai Per Kriteria
            foreach ($challenge->kriteriaPenilaian as $kriteria) {
                $score = $request->nilai[$kriteria->id] ?? 0;
                
                NilaiKriteria::updateOrCreate(
                    [
                        'submisi_id' => $submisi->id,
                        'kriteria_id' => $kriteria->id,
                    ],
                    ['nilai' => $score]
                );

                // Hitung Weighted Score
                $totalScore += ($score * $kriteria->bobot_persen / 100);
                $totalBobot += $kriteria->bobot_persen;
            }

            // 2. Tentukan Grade
            $grade = 'E';
            if ($totalScore >= 85) $grade = 'A';
            else if ($totalScore >= 75) $grade = 'B';
            else if ($totalScore >= 60) $grade = 'C';
            else if ($totalScore >= 50) $grade = 'D';

            // 3. Update Submisi
            $submisi->update([
                'nilai_total' => $totalScore,
                'grade' => $grade,
                'umpan_balik_html' => $request->umpan_balik,
                'status' => 'dinilai',
                'dinilai_pada' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Penilaian berhasil disimpan.');
    }
}