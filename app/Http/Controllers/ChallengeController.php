<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\KategoriProyek;
use App\Http\Requests\Challenge\StoreChallengeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $query = Challenge::query()->with(['pembuat', 'submisi']);

        // Filter Search
        if ($request->search) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        // Filter Status (Default: Buka)
        $status = $request->status ?? 'buka';
        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        // Filter Kategori (JSON Column Logic)
        if ($request->kategori) {
            // Karena kategori_diizinkan bentuknya JSON Array [1, 2], kita pakai JSON_CONTAINS atau similar logic
            // Untuk simpel di Laravel sqlite/mysql modern:
            $query->whereJsonContains('kategori_diizinkan', (int) $request->kategori);
        }

        // Sort: Deadline terdekat untuk yang buka, Terbaru untuk yang lain
        if ($status === 'buka') {
            $query->orderBy('batas_waktu', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $challenges = $query->paginate(9)->withQueryString();

        // Transformasi data untuk frontend (Hitung sisa hari, progress, dll)
        $challenges->getCollection()->transform(function ($challenge) {
            return [
                'id' => $challenge->id,
                'slug' => $challenge->slug,
                'judul' => $challenge->judul,
                'deskripsi' => $challenge->deskripsi,
                'banner' => $challenge->banner,
                'pembuat' => $challenge->pembuat,
                'status' => $challenge->status,
                'batas_waktu' => $challenge->batas_waktu,
                'tanggal_mulai' => $challenge->tanggal_mulai,
                'jumlah_peserta' => $challenge->jumlah_peserta,
                'maks_peserta' => $challenge->maks_peserta,
                'progress_peserta' => $challenge->maks_peserta > 0
                    ? ($challenge->jumlah_peserta / $challenge->maks_peserta) * 100
                    : 0,
                'hadiah' => $challenge->hadiah,
                // Cek apakah user login sudah join
                'is_joined' => auth()->check()
                    ? $challenge->submisi()->where('user_id', auth()->id())->exists()
                    : false,
            ];
        });

        return Inertia::render('Challenge/Index', [
            'challenges' => $challenges,
            'kategoris' => KategoriProyek::select('id', 'nama')->get(),
            'filters' => $request->only(['search', 'status', 'kategori']),
        ]);
    }

    public function create()
    {
        // Cek hak akses
        if (! auth()->user()->hasRole('dosen') && ! auth()->user()->hasRole('superadmin')) {
            abort(403, 'Hanya Dosen yang dapat membuat Challenge.');
        }

        return Inertia::render('Challenge/Buat', [
            'kategoris' => KategoriProyek::where('status_aktif', true)->get(),
        ]);
    }

    public function store(StoreChallengeRequest $request)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // 1. Upload Banner
            $bannerPath = $request->file('banner')->store('challenge/banners', 'public');

            // 2. Simpan Challenge
            $challenge = Challenge::create([
                'pembuat_id' => auth()->id(),
                'judul' => $request->judul,
                'slug' => \Illuminate\Support\Str::slug($request->judul).'-'.\Illuminate\Support\Str::random(5),
                'deskripsi' => $request->deskripsi,
                'aturan_html' => $request->aturan_html,
                'hadiah' => $request->hadiah,
                'banner' => $bannerPath,
                'kategori_diizinkan' => $request->kategori_ids, // Cast otomatis ke JSON di model
                'tanggal_mulai' => $request->tanggal_mulai,
                'batas_waktu' => $request->batas_waktu,
                'maks_peserta' => $request->maks_peserta ?? 0,
                'status' => $request->status,
            ]);

            // 3. Simpan Kriteria Penilaian (Looping)
            foreach ($request->kriteria as $k) {
                $challenge->kriteriaPenilaian()->create([
                    'nama_kriteria' => $k['nama'],
                    'bobot_persen' => $k['bobot'],
                    'deskripsi' => $k['deskripsi'] ?? '',
                ]);
            }
        });

        return redirect()->route('challenge.index')->with('success', 'Challenge berhasil dibuat!');
    }

    public function show($slug)
    {
        $challenge = Challenge::where('slug', $slug)
            ->with(['pembuat.prodi', 'kriteriaPenilaian'])
            ->firstOrFail();

        // Data Peserta (Limit 10 untuk preview)
        $pesertas = $challenge->submisi()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // Cek status user login
        $isJoined = false;
        $mySubmission = null;
        $myProjects = [];

        if (auth()->check()) {
            $submission = $challenge->submisi()->where('user_id', auth()->id())->first();
            if ($submission) {
                $isJoined = true;
                $mySubmission = $submission;
            }

            // Ambil proyek user yang kategorinya diizinkan di challenge ini
            // (Atau ambil semua aja biar simpel, validasi nanti di backend)
            $myProjects = \App\Models\Proyek::where('user_id', auth()->id())
                ->where('status', 'terbit') // Hanya yang sudah terbit
                ->with('kategori')
                ->get();
        }

        return Inertia::render('Challenge/Detail', [
            'challenge' => $challenge,
            'kriterias' => $challenge->kriteriaPenilaian,
            'pesertas' => $pesertas,
            'isJoined' => $isJoined,
            'mySubmission' => $mySubmission,
            'isOwner' => auth()->id() === $challenge->pembuat_id,
            'myProjects' => $myProjects, // Tambahkan ini
        ]);
    }
}
