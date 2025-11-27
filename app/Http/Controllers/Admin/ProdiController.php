<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\ProdiExport;
use Maatwebsite\Excel\Facades\Excel;

class ProdiController extends Controller
{
    // Daftar Fakultas Resmi
    private $listFakultas = [
        'Fakultas Agama Islam',
        'Fakultas Ilmu Pendidikan',
        'Fakultas Sains dan Teknologi'
    ];

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        $prodis = ProgramStudi::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            // Filter Fakultas
            ->when($request->fakultas && $request->fakultas !== 'semua', function ($query) use ($request) {
                $query->where('fakultas', $request->fakultas);
            })
            ->orderBy('fakultas', 'asc')
            ->orderBy('nama', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Prodi/Index', [
            'prodis' => $prodis,
            'filters' => $request->only(['search', 'per_page', 'fakultas']),
            'listFakultas' => $this->listFakultas, // Kirim ke frontend
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:10|unique:program_studis,kode',
            'fakultas' => 'required|string|in:' . implode(',', $this->listFakultas),
        ]);

        // Paksa kode jadi uppercase (misal: inf -> INF)
        $validated['kode'] = strtoupper($validated['kode']);

        ProgramStudi::create($validated);

        return redirect()->back()->with('success', 'Program Studi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $prodi = ProgramStudi::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:10|unique:program_studis,kode,' . $id,
            'fakultas' => 'required|string|in:' . implode(',', $this->listFakultas),
        ]);

        $validated['kode'] = strtoupper($validated['kode']);
        $prodi->update($validated);

        return redirect()->back()->with('success', 'Program Studi diperbarui.');
    }

    public function destroy($id)
    {
        $prodi = ProgramStudi::findOrFail($id);
        
        // Cek apakah ada user (mahasiswa/dosen) di prodi ini
        if ($prodi->users()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal: Masih ada pengguna terdaftar di prodi ini.');
        }

        $prodi->delete();
        return redirect()->back()->with('success', 'Program Studi dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) return redirect()->back()->with('error', 'Tidak ada data dipilih.');

        $count = 0;
        foreach(ProgramStudi::whereIn('id', $ids)->get() as $item) {
             if ($item->users()->count() === 0) {
                 $item->delete();
                 $count++;
             }
        }

        if ($count === 0 && count($ids) > 0) {
            return redirect()->back()->with('error', 'Data terpilih sedang digunakan oleh pengguna.');
        }

        return redirect()->back()->with('success', "$count prodi berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'csv');
        $fakultas = $request->query('fakultas', 'semua');
        $name = "data_prodi_" . now()->format('Y-m-d_H-i');
        return Excel::download(new ProdiExport($fakultas), "$name." . ($type === 'excel' ? 'xlsx' : 'csv'));
    }
}