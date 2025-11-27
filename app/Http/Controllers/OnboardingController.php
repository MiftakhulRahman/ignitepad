<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Peran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function tampilForm()
    {
        // Jika sudah selesai, tendang ke dashboard
        if (auth()->user()->registrasi_selesai) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/LengkapiProfil', [
            'prodis' => ProgramStudi::all(),
        ]);
    }

    public function simpanData(Request $request)
    {
        $request->validate([
            'peran' => 'required|in:dosen,mahasiswa',
            'nomor_induk' => 'required|numeric', // NIM atau NIDN
            'prodi_id' => 'required|exists:program_studis,id',
            'bio' => 'nullable|string|max:500',
            'website_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'github_url' => 'nullable|url',
        ]);

        DB::transaction(function () use ($request) {
            $user = $request->user();
            
            // 1. Update Data User
            $dataUpdate = [
                'prodi_id' => $request->prodi_id,
                'bio' => $request->bio,
                'website_url' => $request->website_url,
                'linkedin_url' => $request->linkedin_url,
                'github_url' => $request->github_url,
                'registrasi_selesai' => true, // TANDAI SELESAI
            ];

            // Set NIM atau NIDN sesuai peran
            if ($request->peran === 'mahasiswa') {
                $dataUpdate['nim'] = $request->nomor_induk;
            } else {
                $dataUpdate['nidn'] = $request->nomor_induk;
            }

            $user->update($dataUpdate);

            // 2. Assign Role
            $role = Peran::where('slug', $request->peran)->first();
            $user->perans()->sync([$role->id]);
        });

        return redirect()->route('dashboard');
    }
}