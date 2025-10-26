<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Profile; // <-- IMPORT PROFILE
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <-- Untuk Bcrypt/Hash
use Illuminate\Validation\Rule; // <-- Untuk Validasi Unique
use Illuminate\Validation\Rules; // <-- Untuk Aturan Password

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user.
     */
    public function index(Request $request) // <-- 1. Tambahkan Request $request
    {
        // 2. Ambil query pencarian
        $search = $request->input('search');

        // 3. Mulai query dasar
        $query = User::query();

        // 4. Terapkan filter pencarian JIKA ada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('nim', 'LIKE', "%{$search}%");
            });
        }

        // 5. Ambil hasil & Paginasi (Urutkan dari terbaru)
        $users = $query->latest()
            ->paginate(10)
            ->appends($request->query()); // <-- 6. Penting untuk paginasi

        // 7. Kirim data ke view
        return view('admin.users.index', compact('users', 'search'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:20', Rule::unique(User::class)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['student', 'lecturer', 'admin'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'graduated'])],
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Verifikasi email otomatis
        $validated['email_verified_at'] = now();

        // 1. Buat User
        $user = User::create($validated);

        // 2. BUAT PROFILE KOSONG UNTUK USER BARU (PENTING!)
        //    Agar halaman profile-nya tidak error
        Profile::create(['user_id' => $user->id]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user yang ada di database.
     */
    public function update(Request $request, User $user)
    {
        // Validasi dasar
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:20', Rule::unique(User::class)->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['student', 'lecturer', 'admin'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'graduated'])],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password opsional
        ]);

        // Cek jika password diisi
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Jika tidak diisi, hapus dari array agar tidak meng-update password
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user dari database.
     */
    public function destroy(User $user)
    {
        // Keamanan: Jangan biarkan admin menghapus akunnya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Hapus file-file terkait (jika ada)
        // (Kita skip dulu agar sederhana, tapi di production ini penting)
        // if ($user->photo) Storage::disk('public')->delete($user->photo);
        // if ($user->profile?->cv_file) Storage::disk('public')->delete($user->profile->cv_file);
        // ... (dan thumbnail proyek, tapi itu sudah di-cascade di Model Project)

        $user->delete(); // Ini akan otomatis men-trigger cascade delete (Proyek, Profil, Like, dll)

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
