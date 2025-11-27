<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriProyek;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use App\Exports\KategoriExport;
use Maatwebsite\Excel\Facades\Excel;

class KategoriController extends Controller
{
    // ... (method index, store, update, toggleStatus, destroy, bulkDestroy TETAP SAMA) ...

    public function index(Request $request)
    {
        // Pengaturan Pagination dinamis
        $perPage = $request->input('per_page', 10);
        
        $kategoris = KategoriProyek::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            // Filter Status (Baru)
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('status_aktif', $request->status);
            })
            ->orderBy('urutan', 'asc') // Urutkan berdasarkan urutan custom
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Kategori/Index', [
            'kategoris' => $kategoris,
            'filters' => $request->only(['search', 'per_page', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_proyeks,nama',
            'warna' => 'required|string|max:7',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'status_aktif' => 'boolean',
            'ikon' => 'nullable|string',
        ]);

        // Auto urutan jika kosong
        if (empty($validated['urutan'])) {
            $validated['urutan'] = (KategoriProyek::max('urutan') ?? 0) + 1;
        }

        KategoriProyek::create([
            'nama' => $validated['nama'],
            'slug' => Str::slug($validated['nama']),
            'warna' => $validated['warna'],
            'ikon' => $validated['ikon'] ?? 'fa-circle',
            'deskripsi' => $validated['deskripsi'],
            'urutan' => $validated['urutan'],
            'status_aktif' => $validated['status_aktif'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriProyek::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_proyeks,nama,' . $id,
            'warna' => 'required|string|max:7',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'status_aktif' => 'boolean',
            'ikon' => 'nullable|string',
        ]);

        $kategori->update([
            'nama' => $validated['nama'],
            'slug' => Str::slug($validated['nama']),
            'warna' => $validated['warna'],
            'deskripsi' => $validated['deskripsi'],
            'urutan' => $validated['urutan'],
            'status_aktif' => $validated['status_aktif'],
            'ikon' => $validated['ikon'],
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    // Method Baru: Toggle Status via AJAX
    public function toggleStatus($id)
    {
        $kategori = KategoriProyek::findOrFail($id);
        $kategori->status_aktif = !$kategori->status_aktif;
        $kategori->save();

        return redirect()->back()->with('success', 'Status kategori diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = KategoriProyek::findOrFail($id);
        
        if ($kategori->proyeks()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal: Kategori sedang digunakan oleh proyek.');
        }

        $kategori->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }

    // --- FITUR BARU: BULK DELETE ---
    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) return redirect()->back()->with('error', 'Tidak ada data dipilih.');

        // Loop delete agar observer/relasi check berjalan (lebih aman)
        $count = 0;
        foreach(KategoriProyek::whereIn('id', $ids)->get() as $item) {
             if ($item->proyeks()->count() === 0) {
                 $item->delete();
                 $count++;
             }
        }

        if ($count === 0 && count($ids) > 0) {
            return redirect()->back()->with('error', 'Data terpilih sedang digunakan dan tidak bisa dihapus.');
        }

        return redirect()->back()->with('success', "$count kategori berhasil dihapus.");
    }

    // --- FITUR BARU: EXPORT EXCEL & CSV (MAATWEBSITE) ---
    public function export(Request $request)
    {
        $type = $request->query('type', 'csv');
        $date = now()->format('Y-m-d_H-i');
        
        if ($type === 'excel') {
            return Excel::download(new KategoriExport, "kategori_proyek_{$date}.xlsx");
        }

        return Excel::download(new KategoriExport, "kategori_proyek_{$date}.csv");
    }
}