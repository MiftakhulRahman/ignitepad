<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PenggunaExport;
use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $tab = $request->input('tab', 'mahasiswa'); // Default tab

        $users = User::query()
            ->with(['prodi', 'perans'])

            // Filter berdasarkan tab (Role)
            ->whereHas('perans', fn($q) => $q->where('slug', $tab))

            // Search
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('nidn', 'like', "%{$search}%");
                });
            })

            // Filter Status
            ->when($request->status !== null, fn($q) => $q->where('status_aktif', $request->status))

            // Filter Prodi (Dosen & Mahasiswa)
            ->when($request->prodi, fn($q) => $q->where('prodi_id', $request->prodi))

            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Pengguna/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page', 'status', 'prodi', 'tab']),
            'roles' => Peran::select('id', 'nama', 'slug')->get(),
            'prodis' => ProgramStudi::select('id', 'nama', 'kode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'peran' => 'required|exists:perans,id',
            'prodi_id' => 'nullable|exists:program_studis,id',
            'nomor_induk' => 'nullable|numeric',
            'status_aktif' => 'boolean',
        ]);

        $userData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'prodi_id' => $validated['prodi_id'],
            'status_aktif' => $validated['status_aktif'] ?? true,
            'registrasi_selesai' => true,
            'email_verified_at' => now(),
        ];

        $role = Peran::find($validated['peran']);

        if ($role->slug === 'mahasiswa') {
            $userData['nim'] = $validated['nomor_induk'];
        }
        if ($role->slug === 'dosen') {
            $userData['nidn'] = $validated['nomor_induk'];
        }

        $user = User::create($userData);
        $user->perans()->attach($validated['peran']);

        return redirect()->back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'peran' => 'required|exists:perans,id',
            'prodi_id' => 'nullable|exists:program_studis,id',
            'nomor_induk' => 'nullable|numeric',
            'status_aktif' => 'boolean',
        ]);

        $userData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'prodi_id' => $validated['prodi_id'],
            'status_aktif' => $validated['status_aktif'],
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        // Reset dulu
        $userData['nim'] = null;
        $userData['nidn'] = null;

        $role = Peran::find($validated['peran']);

        if ($role->slug === 'mahasiswa') {
            $userData['nim'] = $validated['nomor_induk'];
        }
        if ($role->slug === 'dosen') {
            $userData['nidn'] = $validated['nomor_induk'];
        }

        $user->update($userData);
        $user->perans()->sync([$validated['peran']]);

        return redirect()->back()->with('success', 'Data pengguna diperbarui.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $user->status_aktif = !$user->status_aktif;
        $user->save();

        return redirect()->back()->with('success', 'Status pengguna diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Dilarang menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        if (in_array(auth()->id(), $ids)) {
            return redirect()->back()->with('error', 'Salah satu akun yang dipilih adalah akun Anda sendiri.');
        }

        User::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', count($ids) . ' pengguna berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'csv');
        $role = $request->query('tab', 'semua');

        $name = "data_{$role}_" . now()->format('Y-m-d_H-i');

        return Excel::download(new PenggunaExport($role), "$name." . ($type === 'excel' ? 'xlsx' : 'csv'));
    }
}
