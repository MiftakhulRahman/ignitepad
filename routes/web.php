<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Proyek; // Import Model

// --- PUBLIC ROUTES (Bisa diakses tanpa login) ---

// 1. Landing Page (Beranda)
Route::get('/', function () {
    // Ambil 3 proyek terbaru yang statusnya terbit & publik untuk dipajang di depan
    $featured = Proyek::with(['user', 'kategori'])
        ->where('status', 'terbit')
        ->where('visibilitas', 'publik')
        ->latest('terbit_pada')
        ->take(3)
        ->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'featuredProyeks' => $featured,
    ]);
})->name('beranda');

// 2. Jelajah Proyek & Detail (Public Read-Only)
Route::get('/proyek', [App\Http\Controllers\ProyekController::class, 'index'])->name('proyek.index');
Route::get('/proyek/{slug}', [App\Http\Controllers\ProyekController::class, 'show'])->name('proyek.show');

// 3. Challenge List & Detail (Public Read-Only)
Route::get('/challenge', [App\Http\Controllers\ChallengeController::class, 'index'])->name('challenge.index');
Route::get('/challenge/{slug}', [App\Http\Controllers\ChallengeController::class, 'show'])->name('challenge.show');

// --- AUTHENTICATED ROUTES (Harus Login) ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Onboarding
    Route::get('/lengkapi-profil', [App\Http\Controllers\OnboardingController::class, 'tampilForm'])->name('onboarding.form');
    Route::post('/lengkapi-profil', [App\Http\Controllers\OnboardingController::class, 'simpanData'])->name('onboarding.simpan');

    // Area User (Setelah selesai registrasi)
    Route::middleware(['registrasi.selesai'])->group(function () {
        
        Route::get('/dashboard', function () { return Inertia::render('Dashboard'); })->name('dashboard');

        // Proyek (Create/Edit/MyProject)
        Route::get('/proyek/saya', [App\Http\Controllers\ProyekController::class, 'saya'])->name('proyek.saya');
        Route::get('/proyek/buat', [App\Http\Controllers\ProyekController::class, 'create'])->name('proyek.create');
        Route::post('/proyek', [App\Http\Controllers\ProyekController::class, 'store'])->name('proyek.store');
        Route::get('/proyek/{slug}/edit', [App\Http\Controllers\ProyekController::class, 'edit'])->name('proyek.edit');
        Route::post('/proyek/{id}/update', [App\Http\Controllers\ProyekController::class, 'update'])->name('proyek.update');
        Route::delete('/proyek/{id}', [App\Http\Controllers\ProyekController::class, 'destroy'])->name('proyek.destroy');
        
        // Interaksi (Like/Save)
        // Route::post('/proyek/{proyek}/suka', [App\Http\Controllers\InteraksiController::class, 'toggleLike'])->name('proyek.like');
        // Route::post('/proyek/{proyek}/simpan', [App\Http\Controllers\InteraksiController::class, 'toggleSave'])->name('proyek.save');

        // Challenge (Create & Submit)
        Route::get('/challenge/buat', [App\Http\Controllers\ChallengeController::class, 'create'])->name('challenge.create');
        Route::post('/challenge', [App\Http\Controllers\ChallengeController::class, 'store'])->name('challenge.store');
        Route::post('/challenge/{challenge}/join', [App\Http\Controllers\SubmisiController::class, 'join'])->name('challenge.join');
        Route::post('/challenge/{challenge}/submit', [App\Http\Controllers\SubmisiController::class, 'submit'])->name('challenge.submit');
        
        // Challenge Grading
        Route::get('/challenge/{challenge}/kelola', [App\Http\Controllers\SubmisiController::class, 'kelola'])->name('challenge.kelola');
        Route::post('/submisi/{id}/nilai', [App\Http\Controllers\SubmisiController::class, 'nilai'])->name('submisi.nilai');

        // Master Data (Admin)
        
        // CRUD Pengguna
        Route::get('/pengguna', [App\Http\Controllers\Admin\PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [App\Http\Controllers\Admin\PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{id}', [App\Http\Controllers\Admin\PenggunaController::class, 'update'])->name('pengguna.update');
        Route::delete('/pengguna/{id}', [App\Http\Controllers\Admin\PenggunaController::class, 'destroy'])->name('pengguna.destroy');
        Route::patch('/pengguna/{id}/toggle', [App\Http\Controllers\Admin\PenggunaController::class, 'toggleStatus'])->name('pengguna.toggle');
        Route::post('/pengguna/bulk-delete', [App\Http\Controllers\Admin\PenggunaController::class, 'bulkDestroy'])->name('pengguna.bulk-delete');
        Route::get('/pengguna/export', [App\Http\Controllers\Admin\PenggunaController::class, 'export'])->name('pengguna.export');

        // CRUD Kategori
        Route::get('/kategori', [App\Http\Controllers\Admin\KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [App\Http\Controllers\Admin\KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [App\Http\Controllers\Admin\KategoriController::class, 'update'])->name('kategori.update');
        Route::patch('/kategori/{id}/toggle', [App\Http\Controllers\Admin\KategoriController::class, 'toggleStatus'])->name('kategori.toggle');
        Route::delete('/kategori/{id}', [App\Http\Controllers\Admin\KategoriController::class, 'destroy'])->name('kategori.destroy');
        Route::get('/kategori/export', [App\Http\Controllers\Admin\KategoriController::class, 'export'])->name('kategori.export');
        Route::post('/kategori/bulk-delete', [App\Http\Controllers\Admin\KategoriController::class, 'bulkDestroy'])->name('kategori.bulk-delete');

        // CRUD Teknologi
        Route::get('/teknologi', [App\Http\Controllers\Admin\TeknologiController::class, 'index'])->name('teknologi.index');
        Route::post('/teknologi', [App\Http\Controllers\Admin\TeknologiController::class, 'store'])->name('teknologi.store');
        Route::put('/teknologi/{id}', [App\Http\Controllers\Admin\TeknologiController::class, 'update'])->name('teknologi.update');
        Route::delete('/teknologi/{id}', [App\Http\Controllers\Admin\TeknologiController::class, 'destroy'])->name('teknologi.destroy');
        Route::patch('/teknologi/{id}/toggle', [App\Http\Controllers\Admin\TeknologiController::class, 'toggleStatus'])->name('teknologi.toggle');
        Route::post('/teknologi/bulk-delete', [App\Http\Controllers\Admin\TeknologiController::class, 'bulkDestroy'])->name('teknologi.bulk-delete');
        Route::get('/teknologi/export', [App\Http\Controllers\Admin\TeknologiController::class, 'export'])->name('teknologi.export');

        // CRUD Prodi
        Route::get('/program-studi', [App\Http\Controllers\Admin\ProdiController::class, 'index'])->name('prodi.index');
        Route::post('/program-studi', [App\Http\Controllers\Admin\ProdiController::class, 'store'])->name('prodi.store');
        Route::put('/program-studi/{id}', [App\Http\Controllers\Admin\ProdiController::class, 'update'])->name('prodi.update');
        Route::delete('/program-studi/{id}', [App\Http\Controllers\Admin\ProdiController::class, 'destroy'])->name('prodi.destroy');
        Route::post('/program-studi/bulk-delete', [App\Http\Controllers\Admin\ProdiController::class, 'bulkDestroy'])->name('prodi.bulk-delete');
        Route::get('/program-studi/export', [App\Http\Controllers\Admin\ProdiController::class, 'export'])->name('prodi.export');

        // Profil
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';