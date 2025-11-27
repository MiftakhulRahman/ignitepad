<?php

namespace App\Http\Requests\Proyek;

use Illuminate\Foundation\Http\FormRequest;

class StoreProyekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Izinkan semua user login
    }

    public function rules(): array
    {
        return [
            // Identitas
            'judul' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_proyeks,id',
            'deskripsi' => 'required|string|max:255', // Deskripsi singkat
            'konten_html' => 'required|string', // Konten lengkap
            
            // File Upload
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Max 2MB
            'galeri' => 'nullable|array|max:5', // Max 5 gambar tambahan
            'galeri.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Relasi & Meta
            'teknologi_ids' => 'required|array|min:1', // Minimal pilih 1 teknologi
            'teknologi_ids.*' => 'exists:teknologis,id',
            'url_demo' => 'nullable|url',
            'url_repository' => 'nullable|url',
            
            // Settings
            'status' => 'required|in:draft,terbit',
            'visibilitas' => 'required|in:publik,privat',
            'boleh_komentar' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'judul.required' => 'Judul proyek wajib diisi.',
            'kategori_id.required' => 'Pilih kategori proyek.',
            'thumbnail.required' => 'Thumbnail proyek wajib diupload.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
            'teknologi_ids.required' => 'Pilih minimal satu teknologi/tools.',
        ];
    }
}