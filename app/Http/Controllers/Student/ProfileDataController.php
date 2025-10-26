<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile; // Import model Profile

class ProfileDataController extends Controller
{
    /**
     * Update the user's profile details (bio, links, skills, etc.).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi input
        $validated = $request->validate([
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'cv_file' => 'nullable|file|mimes:pdf|max:2048', // 2MB max
            'achievements' => 'nullable|string',
        ]);

        // 2. Siapkan data untuk disimpan
        $dataToUpdate = [
            'bio' => $validated['bio'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            
            // Proses string "Laravel, React" menjadi array JSON
            'skills' => $validated['skills'] 
                ? array_filter(array_map('trim', explode(',', $validated['skills']))) 
                : [],
            
            // Proses string "Juara 1\nJuara 2" menjadi array JSON
            'achievements' => $validated['achievements'] 
                ? array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['achievements']))) 
                : [],
        ];

        // 3. Dapatkan profil (atau buat baru jika belum ada)
        // updateOrCreate = Jika user_id sudah ada, update. Jika belum, buat baru.
        $profile = Profile::firstOrNew(['user_id' => $user->id]);

        // 4. Handle Upload CV
        if ($request->hasFile('cv_file')) {
            // Hapus CV lama jika ada
            if ($profile->cv_file) {
                Storage::disk('public')->delete($profile->cv_file);
            }
            // Simpan CV baru di storage/app/public/cvs
            $path = $request->file('cv_file')->store('cvs', 'public');
            $dataToUpdate['cv_file'] = $path;
        }

        // 5. Simpan data ke database
        $profile->fill($dataToUpdate);
        $profile->save();

        return Redirect::route('profile.edit')->with('status', 'profile-details-updated');
    }
}