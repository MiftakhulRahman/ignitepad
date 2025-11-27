<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Halaman Depan (Landing Page)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Grouping Route yang butuh Login
Route::middleware(['auth', 'verified'])->group(function () {

    // --- ONBOARDING (User baru masuk sini dulu) ---
    Route::get('/lengkapi-profil', [App\Http\Controllers\OnboardingController::class, 'tampilForm'])
        ->name('onboarding.form');
    Route::post('/lengkapi-profil', [App\Http\Controllers\OnboardingController::class, 'simpanData'])
        ->name('onboarding.simpan');

    // --- AREA YANG DIKUNCI (Harus selesai registrasi) ---
    Route::middleware(['registrasi.selesai'])->group(function () {

        // Dashboard Utama
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // --- PROYEK ---
        Route::get('/proyek', function () {
            return Inertia::render('Dashboard');
        })->name('proyek.index');

        Route::get('/proyek/saya', function () {
            return Inertia::render('Dashboard');
        })->name('proyek.saya');

        // --- CHALLENGE ---
        Route::get('/challenge', function () {
            return Inertia::render('Dashboard');
        })->name('challenge.index');

        // --- MASTER DATA ---
        // CRUD Pengguna (User Management)
        Route::get('/pengguna', [App\Http\Controllers\Admin\PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [App\Http\Controllers\Admin\PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{id}', [App\Http\Controllers\Admin\PenggunaController::class, 'update'])->name('pengguna.update');
        Route::delete('/pengguna/{id}', [App\Http\Controllers\Admin\PenggunaController::class, 'destroy'])->name('pengguna.destroy');
        Route::patch('/pengguna/{id}/toggle', [App\Http\Controllers\Admin\PenggunaController::class, 'toggleStatus'])->name('pengguna.toggle');
        Route::post('/pengguna/bulk-delete', [App\Http\Controllers\Admin\PenggunaController::class, 'bulkDestroy'])->name('pengguna.bulk-delete');
        Route::get('/pengguna/export', [App\Http\Controllers\Admin\PenggunaController::class, 'export'])->name('pengguna.export');

        // CRUD Kategori
        Route::get('/kategori', [App\Http\Controllers\Admin\KategoriController::class, 'index'])
            ->name('kategori.index');
        Route::post('/kategori', [App\Http\Controllers\Admin\KategoriController::class, 'store'])
            ->name('kategori.store');
        Route::put('/kategori/{id}', [App\Http\Controllers\Admin\KategoriController::class, 'update'])
            ->name('kategori.update');
        Route::patch('/kategori/{id}/toggle', [App\Http\Controllers\Admin\KategoriController::class, 'toggleStatus'])->name('kategori.toggle');
        Route::delete('/kategori/{id}', [App\Http\Controllers\Admin\KategoriController::class, 'destroy'])
            ->name('kategori.destroy');
        Route::get('/kategori/export', [App\Http\Controllers\Admin\KategoriController::class, 'export'])->name('kategori.export');
        Route::post('/kategori/bulk-delete', [App\Http\Controllers\Admin\KategoriController::class, 'bulkDestroy'])->name('kategori.bulk-delete');

        // CRUD Teknologi - Ultimate
        Route::get('/teknologi', [App\Http\Controllers\Admin\TeknologiController::class, 'index'])->name('teknologi.index');
        Route::post('/teknologi', [App\Http\Controllers\Admin\TeknologiController::class, 'store'])->name('teknologi.store');
        Route::put('/teknologi/{id}', [App\Http\Controllers\Admin\TeknologiController::class, 'update'])->name('teknologi.update');
        Route::delete('/teknologi/{id}', [App\Http\Controllers\Admin\TeknologiController::class, 'destroy'])->name('teknologi.destroy');

        // Fitur Tambahan
        Route::patch('/teknologi/{id}/toggle', [App\Http\Controllers\Admin\TeknologiController::class, 'toggleStatus'])->name('teknologi.toggle');
        Route::post('/teknologi/bulk-delete', [App\Http\Controllers\Admin\TeknologiController::class, 'bulkDestroy'])->name('teknologi.bulk-delete');
        Route::get('/teknologi/export', [App\Http\Controllers\Admin\TeknologiController::class, 'export'])->name('teknologi.export');

        // CRUD Program Studi
        Route::get('/program-studi', [App\Http\Controllers\Admin\ProdiController::class, 'index'])->name('prodi.index');
        Route::post('/program-studi', [App\Http\Controllers\Admin\ProdiController::class, 'store'])->name('prodi.store');
        Route::put('/program-studi/{id}', [App\Http\Controllers\Admin\ProdiController::class, 'update'])->name('prodi.update');
        Route::delete('/program-studi/{id}', [App\Http\Controllers\Admin\ProdiController::class, 'destroy'])->name('prodi.destroy');
        Route::post('/program-studi/bulk-delete', [App\Http\Controllers\Admin\ProdiController::class, 'bulkDestroy'])->name('prodi.bulk-delete');
        Route::get('/program-studi/export', [App\Http\Controllers\Admin\ProdiController::class, 'export'])->name('prodi.export');

        // Profil User
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
