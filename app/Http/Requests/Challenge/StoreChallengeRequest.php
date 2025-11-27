<?php

namespace App\Http\Requests\Challenge;

use Illuminate\Foundation\Http\FormRequest;

class StoreChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya Dosen (dan Admin) yang boleh buat
        return $this->user()->hasRole('dosen') || $this->user()->hasRole('superadmin');
    }

    public function rules(): array
    {
        return [
            // Info Dasar
            'judul' => 'required|string|max:255',
            'kategori_ids' => 'required|array|min:1', // Kategori proyek yang diizinkan
            'banner' => 'required|image|max:2048',
            'deskripsi' => 'required|string',
            
            // Aturan & Hadiah
            'aturan_html' => 'required|string',
            'hadiah' => 'required|string|max:255',
            
            // Jadwal
            'tanggal_mulai' => 'required|date',
            'batas_waktu' => 'required|date|after:tanggal_mulai',
            'maks_peserta' => 'nullable|integer|min:0', // 0 = unlimited
            
            // Kriteria Penilaian (Dynamic Array)
            'kriteria' => 'required|array|min:1',
            'kriteria.*.nama' => 'required|string',
            'kriteria.*.bobot' => 'required|numeric|min:1|max:100',
            
            'status' => 'required|in:draft,buka',
        ];
    }

    public function messages()
    {
        return [
            'kriteria.required' => 'Minimal harus ada satu kriteria penilaian.',
            'batas_waktu.after' => 'Deadline harus setelah tanggal mulai.',
        ];
    }
}