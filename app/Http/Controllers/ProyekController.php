<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proyek\StoreProyekRequest;
use App\Models\KategoriProyek;
use App\Models\Proyek;
use App\Models\Kolaborator;
use App\Models\Teknologi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->with(['kategori', 'teknologi', 'kolaborators.user', 'user.prodi'])
            ->withCount('komentar')
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

    // Proses Simpan Proyek Baru
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'judul' => 'required|max:255',
            'kategori_id' => 'required|exists:kategori_proyeks,id',
            'deskripsi' => 'nullable|max:255',
            'konten_html' => 'required',
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png|max:1024|dimensions:min_width=800,min_height=450',
            'galeri' => 'nullable|array|max:2',
            'galeri.*' => 'image|mimes:jpg,jpeg,png|max:1024',
            'teknologi_ids' => 'required|array|min:1',
            'teknologi_ids.*' => 'exists:teknologis,id',
            'url_demo' => 'nullable|url',
            'url_repository' => 'nullable|url',
            'status' => 'in:draft,terbit',
            'visibilitas' => 'in:publik,terbatas,privat',
            'boleh_komentar' => 'boolean',
            'kolaborators' => 'nullable|array',
        ]);

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

            // 5. Simpan Kolaborator
            if ($request->has('kolaborators')) {
                foreach ($request->kolaborators as $k) {
                    Kolaborator::create([
                        'proyek_id' => $proyek->id,
                        'user_id' => $k['user_id'],
                        'peran_kolaborator' => $k['peran_kolaborator'],
                        'bisa_edit' => $k['bisa_edit'] ?? false,
                        'bisa_hapus' => $k['bisa_hapus'] ?? false,
                        'status_undangan' => 'diterima',
                    ]);
                }
            }
        });

        return redirect()->route('proyek.saya')->with('success', 'Proyek berhasil dibuat!');
    }

    // Halaman Detail Proyek (Public View)
    public function show($slug)
    {
        $proyek = Proyek::where('slug', $slug)
            ->with(['user.prodi', 'kategori', 'teknologi', 'komentar.user', 'kolaborators.user'])
            ->firstOrFail();

        // Hitung View (Simpel)
        $proyek->increment('jumlah_lihat');

        $user = auth()->user();
        $isOwner = $user && $user->id === $proyek->user_id;
        $isAdmin = $user && $user->perans->contains('slug', 'superadmin');

        return Inertia::render('Proyek/Detail', [
            'proyek' => $proyek,
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'hasLiked' => $user ? $proyek->isLikedBy($user) : false,
            'hasSaved' => $user ? \App\Models\Simpanan::where('user_id', $user->id)->where('proyek_id', $proyek->id)->exists() : false,
            'komentars' => $proyek->komentar()
                ->whereNull('induk_id') // Hanya ambil komentar induk
                ->with(['user', 'likes', 'dislikes', 'balasan.user', 'balasan.likes', 'balasan.dislikes']) // Eager load balasan, likes & dislikes
                ->withCount(['likes', 'dislikes']) // Count likes and dislikes
                ->latest()
                ->get()
                ->map(function ($komentar) use ($user) {
                    $komentar->is_liked = $user ? $komentar->likes->contains('user_id', $user->id) : false;
                    $komentar->is_disliked = $user ? $komentar->dislikes->contains('user_id', $user->id) : false;
                    $komentar->jumlah_suka = $komentar->likes_count;
                    $komentar->jumlah_dislikes = $komentar->dislikes_count;
                    
                    // Map balasan juga
                    $komentar->balasan = $komentar->balasan->map(function ($balasan) use ($user) {
                        $balasan->is_liked = $user ? $balasan->likes->contains('user_id', $user->id) : false;
                        $balasan->is_disliked = $user ? $balasan->dislikes->contains('user_id', $user->id) : false;
                        $balasan->jumlah_suka = $balasan->likes->count();
                        $balasan->jumlah_dislikes = $balasan->dislikes->count();
                        return $balasan;
                    });
                    
                    return $komentar;
                }),
        ]);
    }

    // Form Edit Proyek
    public function edit($slug)
    {
        $proyek = Proyek::where('slug', $slug)
            ->with(['teknologi', 'kolaborators.user'])
            ->firstOrFail();

        // Authorization: Hanya Pemilik atau Admin yang boleh edit
        if (auth()->id() !== $proyek->user_id && ! auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit proyek ini.');
        }

        return Inertia::render('Proyek/Edit', [
            'proyek' => $proyek,
            'kategoris' => KategoriProyek::where('status_aktif', true)->get(),
            'teknologis' => Teknologi::where('status_aktif', true)->get(),
            'currentTeknologiIds' => $proyek->teknologi->pluck('id'),
            'kolaborators' => $proyek->kolaborators()
                ->where('status_undangan', 'diterima')
                ->with('user')
                ->get(),
        ]);
    }

    // Proses Update Proyek
    public function update(Request $request, $id)
    {
        $proyek = Proyek::findOrFail($id);

        if (auth()->id() !== $proyek->user_id && ! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        // Validation
        $request->validate([
            'judul' => 'required|max:255',
            'kategori_id' => 'required|exists:kategori_proyeks,id',
            'deskripsi' => 'nullable|max:255',
            'konten_html' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:1024|dimensions:min_width=800,min_height=450',
            'galeri' => 'nullable|array|max:2',
            'galeri.*' => 'image|mimes:jpg,jpeg,png|max:1024',
            'existing_galeri' => 'nullable|array|max:2',
            'teknologi_ids' => 'required|array|min:1',
            'teknologi_ids.*' => 'exists:teknologis,id',
            'url_demo' => 'nullable|url',
            'url_repository' => 'nullable|url',
            'status' => 'in:draft,terbit',
            'visibilitas' => 'in:publik,terbatas,privat',
            'boleh_komentar' => 'boolean',
            'kolaborators' => 'nullable|array',
        ]);

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

            // 2. Handle Galeri (existing + new, max 5 total)
            // Start with existing gallery from request (those not deleted by user)
            $galeriPaths = is_array($request->existing_galeri) ? $request->existing_galeri : [];
            
            // Delete old gallery images that user removed
            if ($proyek->galeri_gambar) {
                $removedImages = array_diff($proyek->galeri_gambar, $galeriPaths);
                foreach ($removedImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            
            // Add new uploaded images (limit total to 2)
            if ($request->hasFile('galeri')) {
                $remainingSlots = 2 - count($galeriPaths);
                $newFiles = array_slice($request->file('galeri'), 0, $remainingSlots);
                
                foreach ($newFiles as $img) {
                    $path = $img->store('proyek/galeri', 'public');
                    $galeriPaths[] = $path;
                }
            }

            // 3. Update Data
            $proyek->update([
                'kategori_id' => $request->kategori_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'konten_html' => $request->konten_html,
                'galeri_gambar' => $galeriPaths, // Update gallery
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

            // 5. Sync Kolaborator
            if ($request->has('kolaborators')) {
                // Hapus semua kolaborator lama (simple sync strategy)
                $proyek->kolaborators()->delete();

                // Buat ulang dari list baru
                foreach ($request->kolaborators as $k) {
                    Kolaborator::create([
                        'proyek_id' => $proyek->id,
                        'user_id' => $k['user_id'],
                        'peran_kolaborator' => $k['peran_kolaborator'],
                        'bisa_edit' => $k['bisa_edit'] ?? false,
                        'bisa_hapus' => $k['bisa_hapus'] ?? false,
                        'status_undangan' => 'diterima',
                    ]);
                }
            }
        });

        return redirect()->route('proyek.saya')->with('success', 'Proyek berhasil diperbarui.');
    }

    // Halaman Detail Proyek (Dashboard Preview - No View Increment)
    public function dashboardShow($slug)
    {
        $proyek = Proyek::where('slug', $slug)
            ->with(['user.prodi', 'kategori', 'teknologi', 'kolaborators.user'])
            ->firstOrFail();

        $user = auth()->user();

        // Authorization check
        $isOwner = $user && $user->id === $proyek->user_id;
        $isAdmin = $user && $user->perans->contains('slug', 'superadmin');
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return Inertia::render('Proyek/Dashboard/ProyekShow', [
            'proyek' => $proyek,
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'kolaborators' => $proyek->kolaborators()
                ->where('status_undangan', 'diterima')
                ->with('user')
                ->get(),
        ]);
    }

    // API: Search Users untuk Kolaborator
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('nim', 'like', "%{$query}%")
                  ->orWhere('nidn', 'like', "%{$query}%");
            })
            ->with('perans')
            ->limit(10)
            ->get(['id', 'nama', 'username', 'email', 'nim', 'nidn', 'avatar']);

        return response()->json($users);
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
