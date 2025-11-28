<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proyek\StoreProyekRequest;
use App\Models\KategoriProyek;
use App\Models\Proyek;
use App\Models\Teknologi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProyekController extends Controller
{
    // Halaman List Proyek (Gallery)
    public function index(Request $request)
    {
        $user = auth()->user();

        // Cek apakah user adalah Superadmin (Handle Guest)
        $isAdmin = $user && $user->perans->contains('slug', 'superadmin');

        $query = Proyek::query()->with(['user', 'kategori', 'teknologi']);

        // LOGIKA FILTER:
        if ($isAdmin) {
            // ADMIN: Bisa lihat semua, dan bisa filter berdasarkan status
            // Jika ada request 'status', filter by status. Jika tidak, tampilkan semua.
            if ($request->status) {
                $query->where('status', $request->status);
            }
        } else {
            // PUBLIC/MAHASISWA/DOSEN: Hanya lihat yang terbit & publik
            // Kecuali proyek miliknya sendiri (opsional, biasanya punya halaman sendiri)
            $query->where('status', 'terbit')
                ->where('visibilitas', 'publik');
        }

        // Search Filter (Tambahan)
        if ($request->search) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $proyeks = $query->latest('terbit_pada')
            ->latest('created_at') // Fallback sort buat draft
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Proyek/Index', [
            'proyeks' => $proyeks,
            'filters' => $request->only(['search', 'status']),
            'isAdmin' => $isAdmin, // Kirim status admin ke frontend
        ]);
    }

    // Halaman "Proyek Saya" (Dashboard User)
    public function saya(Request $request)
    {
        $user = auth()->user();

        $proyeks = Proyek::where('user_id', $user->id)
            ->with(['kategori'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Proyek/Saya', [
            'proyeks' => $proyeks,
        ]);
    }

    // Form Buat Proyek Baru
    public function create()
    {
        return Inertia::render('Proyek/Buat', [
            'kategoris' => KategoriProyek::where('status_aktif', true)->orderBy('nama')->get(),
            'teknologis' => Teknologi::where('status_aktif', true)->orderBy('nama')->get(),
        ]);
    }

    // Proses Simpan Proyek
    public function store(StoreProyekRequest $request)
    {
        DB::transaction(function () use ($request) {
            // 1. Upload Thumbnail
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('proyek/thumbnails', 'public');
            }

            // 2. Upload Galeri (Multiple)
            $galeriPaths = [];
            if ($request->hasFile('galeri')) {
                foreach ($request->file('galeri') as $file) {
                    $galeriPaths[] = $file->store('proyek/galeri', 'public');
                }
            }

            // 3. Simpan Data Utama
            $proyek = Proyek::create([
                'user_id' => auth()->id(),
                'kategori_id' => $request->kategori_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'konten_html' => $request->konten_html,
                'thumbnail' => $thumbnailPath, // Path file
                'galeri_gambar' => $galeriPaths, // Array path (otomatis jadi JSON karena casting di Model)
                'url_demo' => $request->url_demo,
                'url_repository' => $request->url_repository,
                'status' => $request->status,
                'visibilitas' => $request->visibilitas,
                'boleh_komentar' => $request->boleh_komentar,
                'terbit_pada' => $request->status === 'terbit' ? now() : null,
            ]);

            // 4. Hubungkan dengan Teknologi (Pivot Table)
            $proyek->teknologi()->attach($request->teknologi_ids);
        });

        return redirect()->route('proyek.saya')->with('success', 'Proyek berhasil dibuat!');
    }

    // Halaman Detail Proyek
    public function show($slug)
    {
        $proyek = Proyek::where('slug', $slug)
            ->with(['user.prodi', 'kategori', 'teknologi', 'komentar.user'])
            ->firstOrFail();

        // Hitung View (Simpel)
        $proyek->increment('jumlah_lihat');

        $user = auth()->user();

        return Inertia::render('Proyek/Detail', [
            'proyek' => $proyek,
            'isOwner' => $user && $user->id === $proyek->user_id,
            'hasLiked' => $user ? $proyek->isLikedBy($user) : false,
            // Cek manual simpanan jika belum ada method isSavedBy
            'hasSaved' => $user ? \App\Models\Simpanan::where('user_id', $user->id)->where('proyek_id', $proyek->id)->exists() : false,
            'komentars' => $proyek->komentar()
                ->whereNull('induk_id') // Hanya ambil komentar induk
                ->with(['user', 'likes', 'balasan.user', 'balasan.likes']) // Eager load balasan & likes
                ->latest()
                ->get()
                ->map(function ($komentar) use ($user) {
                    $komentar->is_liked = $user ? $komentar->likes->contains('user_id', $user->id) : false;
                    
                    // Map balasan juga
                    $komentar->balasan = $komentar->balasan->map(function ($balasan) use ($user) {
                        $balasan->is_liked = $user ? $balasan->likes->contains('user_id', $user->id) : false;
                        return $balasan;
                    });
                    
                    return $komentar;
                }),
        ]);
    }

    // Form Edit Proyek
    public function edit($slug)
    {
        $proyek = Proyek::where('slug', $slug)->with('teknologi')->firstOrFail();

        // Authorization: Hanya Pemilik atau Admin yang boleh edit
        if (auth()->id() !== $proyek->user_id && ! auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit proyek ini.');
        }

        return Inertia::render('Proyek/Edit', [
            'proyek' => $proyek,
            'kategoris' => KategoriProyek::where('status_aktif', true)->get(),
            'teknologis' => Teknologi::where('status_aktif', true)->get(),
            'currentTeknologiIds' => $proyek->teknologi->pluck('id'), // Untuk pre-fill checkbox
        ]);
    }

    // Proses Update Proyek
    // Gunakan Request yang sama atau buat UpdateProyekRequest (bisa reuse StoreProyekRequest dengan sedikit modifikasi)
    public function update(StoreProyekRequest $request, $id)
    {
        $proyek = Proyek::findOrFail($id);

        if (auth()->id() !== $proyek->user_id && ! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        DB::transaction(function () use ($request, $proyek) {
            // 1. Handle Thumbnail Baru (Jika ada)
            if ($request->hasFile('thumbnail')) {
                // Hapus file lama
                if ($proyek->thumbnail) {
                    Storage::disk('public')->delete($proyek->thumbnail);
                }
                $thumbnailPath = $request->file('thumbnail')->store('proyek/thumbnails', 'public');
                $proyek->thumbnail = $thumbnailPath;
            }

            // 2. Handle Galeri Baru (Append/Replace logic bisa kompleks, kita buat simpel append dulu)
            if ($request->hasFile('galeri')) {
                $currentGaleri = $proyek->galeri_gambar ?? [];
                foreach ($request->file('galeri') as $file) {
                    $currentGaleri[] = $file->store('proyek/galeri', 'public');
                }
                $proyek->galeri_gambar = $currentGaleri;
            }

            // 3. Update Data
            $proyek->update([
                'kategori_id' => $request->kategori_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'konten_html' => $request->konten_html,
                'url_demo' => $request->url_demo,
                'url_repository' => $request->url_repository,
                'status' => $request->status,
                'visibilitas' => $request->visibilitas,
                'boleh_komentar' => $request->boleh_komentar,
                // Update timestamp terbit jika baru diterbitkan sekarang
                'terbit_pada' => ($proyek->status !== 'terbit' && $request->status === 'terbit') ? now() : $proyek->terbit_pada,
            ]);

            // 4. Sync Teknologi
            $proyek->teknologi()->sync($request->teknologi_ids);
        });

        return redirect()->route('proyek.show', $proyek->slug)->with('success', 'Proyek berhasil diperbarui.');
    }

    // Hapus Proyek
    public function destroy($id)
    {
        $proyek = Proyek::findOrFail($id);

        if (auth()->id() !== $proyek->user_id && ! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        // Hapus File Gambar
        if ($proyek->thumbnail) {
            Storage::disk('public')->delete($proyek->thumbnail);
        }
        if ($proyek->galeri_gambar) {
            foreach ($proyek->galeri_gambar as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $proyek->delete();

        // Redirect sesuai role
        if (auth()->user()->hasRole('superadmin')) {
            return redirect()->route('proyek.index')->with('success', 'Proyek dihapus.');
        }

        return redirect()->route('proyek.saya')->with('success', 'Proyek dihapus.');
    }
}
