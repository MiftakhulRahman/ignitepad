<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teknologi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Exports\TeknologiExport;
use Maatwebsite\Excel\Facades\Excel;

class TeknologiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        $teknologis = Teknologi::query()
            // 1. Filter Search
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            // 2. Filter Status
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('status_aktif', $request->status);
            })
            // 3. Filter Jenis Teknologi (Framework, Language, dll)
            ->when($request->jenis, function ($query, $jenis) {
                $query->where('kategori_teknologi', $jenis);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Ambil list unik kategori teknologi untuk dropdown filter
        $jenisOptions = Teknologi::select('kategori_teknologi')->distinct()->pluck('kategori_teknologi');

        return Inertia::render('Admin/Teknologi/Index', [
            'teknologis' => $teknologis,
            'jenisOptions' => $jenisOptions,
            'filters' => $request->only(['search', 'per_page', 'status', 'jenis']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:teknologis,nama',
            'ikon_url' => 'required|url',
            'kategori_teknologi' => 'required|string', // Framework, Language, dll
            'warna' => 'nullable|string|max:7',
            'status_aktif' => 'boolean',
        ]);

        Teknologi::create([
            'nama' => $validated['nama'],
            'slug' => Str::slug($validated['nama']),
            'ikon_url' => $validated['ikon_url'],
            'kategori_teknologi' => $validated['kategori_teknologi'],
            'warna' => $validated['warna'] ?? '#64748b',
            'status_aktif' => $validated['status_aktif'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Teknologi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $teknologi = Teknologi::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:teknologis,nama,' . $id,
            'ikon_url' => 'required|url',
            'kategori_teknologi' => 'required|string',
            'warna' => 'nullable|string|max:7',
            'status_aktif' => 'boolean',
        ]);

        $teknologi->update([
            'nama' => $validated['nama'],
            'slug' => Str::slug($validated['nama']),
            'ikon_url' => $validated['ikon_url'],
            'kategori_teknologi' => $validated['kategori_teknologi'],
            'warna' => $validated['warna'],
            'status_aktif' => $validated['status_aktif'],
        ]);

        return redirect()->back()->with('success', 'Teknologi berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $teknologi = Teknologi::findOrFail($id);
        $teknologi->status_aktif = !$teknologi->status_aktif;
        $teknologi->save();

        return redirect()->back()->with('success', 'Status teknologi diperbarui.');
    }

    public function destroy($id)
    {
        $teknologi = Teknologi::findOrFail($id);
        
        // Cek relasi ke proyek
        if ($teknologi->proyeks()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal: Teknologi sedang digunakan oleh proyek.');
        }

        $teknologi->delete();
        return redirect()->back()->with('success', 'Teknologi berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) return redirect()->back()->with('error', 'Tidak ada data dipilih.');

        $count = 0;
        foreach(Teknologi::whereIn('id', $ids)->get() as $item) {
             if ($item->proyeks()->count() === 0) {
                 $item->delete();
                 $count++;
             }
        }

        if ($count === 0 && count($ids) > 0) {
            return redirect()->back()->with('error', 'Data terpilih sedang digunakan di proyek.');
        }

        return redirect()->back()->with('success', "$count teknologi berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'csv');
        $date = now()->format('Y-m-d_H-i');
        
        if ($type === 'excel') {
            return Excel::download(new TeknologiExport, "teknologi_{$date}.xlsx");
        }

        return Excel::download(new TeknologiExport, "teknologi_{$date}.csv");
    }
}