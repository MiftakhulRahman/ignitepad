<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
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
        Route::get('/pengguna', function () { 
            return Inertia::render('Dashboard'); 
        })->name('pengguna.index');

        Route::get('/kategori', function () { 
            return Inertia::render('Dashboard'); 
        })->name('kategori.index');

        Route::get('/teknologi', function () { 
            return Inertia::render('Dashboard'); 
        })->name('teknologi.index');

        // Profil User
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
