<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectReviewController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TechnologyController as AdminTechnologyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProjectController as StudentProjectController;
use App\Http\Controllers\Student\ProfileDataController;
use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboardController;
use App\Http\Controllers\Lecturer\ReviewController;
use App\Http\Controllers\Lecturer\NotificationController as LecturerNotificationController;
use App\Http\Controllers\NotificationController;

// 1. IMPORT BARU UNTUK RUTE PUBLIK
use App\Http\Controllers\CategoryController; 
use App\Http\Controllers\TechnologyController; 

use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\BookmarkController as ApiBookmarkController; // <-- Alias untuk API
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\Student\ProjectImageController; // <-- Import untuk fitur hapus screenshot


// --- RUTE PUBLIK (Bisa diakses siapa saja) ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rute untuk Project
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

// 2. TAMBAHKAN RUTE BARU INI UNTUK KATEGORI DAN TEKNOLOGI
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/technologies/{technology:slug}', [TechnologyController::class, 'show'])->name('technologies.show');

// Rute untuk Halaman Profil Publik
Route::get('/@{user:username}', [ProfileController::class, 'publicShow'])->name('profile.public.show');

// Route for checking username availability
Route::get('/check-username', [App\Http\Controllers\CheckUsernameController::class, 'check'])->name('check-username');


// --- RUTE AUTENTIKASI (Harus Login) ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Rute /dashboard bawaan Breeze
    // Kita jadikan "Pengarah Otomatis" berdasarkan Role
    Route::get('/dashboard', [HomeController::class, 'dashboardRedirect'])->name('dashboard');

    // Rute Profil (Bawaan Breeze, sudah berfungsi)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/username', [ProfileController::class, 'updateUsername'])->name('profile.username.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute untuk meng-update data di tabel 'profiles'
    Route::patch('/profile/details', [ProfileDataController::class, 'update'])->name('profile.details.update');

    // Halaman untuk menampilkan daftar bookmark
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

    // Rute Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark_all_read');
    Route::post('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy_all');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');


    // Rute untuk HAPUS 1 screenshot
    Route::delete('/student/projects/image/{projectImage}', [ProjectImageController::class, 'destroy'])
        ->name('student.projects.image.destroy');

    // --- GRUP ADMIN ---
    // Hanya bisa diakses oleh user dengan role 'admin'
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('technologies', AdminTechnologyController::class);
        Route::resource('users', UserController::class);
        Route::resource('projects', AdminProjectController::class)->only([
            'index', 'edit', 'update', 'destroy'
        ]);
    });

    // --- GRUP MAHASISWA (STUDENT) ---
    // Hanya bisa diakses oleh user dengan role 'student'
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::resource('projects', StudentProjectController::class);
    });

    // --- GRUP DOSEN (LECTURER) ---
    // IZINKAN ADMIN MENGAKSES INI JUGA
    Route::prefix('lecturer')->name('lecturer.')->middleware('role:lecturer,admin')->group(function () {
        Route::get('/dashboard', [LecturerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{project}', [ReviewController::class, 'show'])->name('reviews.show');
        Route::patch('/reviews/{project}', [ReviewController::class, 'update'])->name('reviews.update');

        // Route untuk menandai notifikasi sebagai sudah dibaca dan redirect
        Route::get('/notifications/{notification}/read', [LecturerNotificationController::class, 'read'])->name('notifications.read');


    });

    Route::post('/projects/{project}/reviews', [ProjectReviewController::class, 'store'])
        ->name('projects.reviews.store');
});

// --- Rute API ---
// Rute-rute ini HARUS login, tapi kita pakai 'auth' saja
// agar bisa diakses via AJAX (tidak perlu 'verified' di sini)
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::post('/projects/{project}/like', [LikeController::class, 'toggle'])
        ->name('projects.like');

    Route::post('/projects/{project}/bookmark', [ApiBookmarkController::class, 'toggle'])
        ->name('projects.bookmark');

    Route::get('/users/search', [\App\Http\Controllers\Api\UserSearchController::class, 'search'])
        ->name('users.search');
});

// Memuat rute-rute auth bawaan Breeze (Login, Register, Lupa Password, dll)
require __DIR__ . '/auth.php';