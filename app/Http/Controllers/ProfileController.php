<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's public profile.
     */
    public function publicShow(User $user): View
    {
        // Eager load relasi yang dibutuhkan untuk halaman profil publik
        $user->loadCount(['projects', 'likes']);
        $user->load(['profile', 'projects' => function ($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public')
                  ->latest()
                  ->take(6); // Ambil 6 proyek terbaru saja
        }]);

        return view('profile.public-show', compact('user'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            // Simpan foto baru di storage/app/public/avatars
            $path = $request->file('photo')->store('avatars', 'public');
            $validatedData['photo'] = $path;
        }

        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's username via AJAX.
     */
    public function updateUsername(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->username = $request->input('username');
        $user->save();

        return response()->json(['message' => __('Username Anda telah diperbarui.')], 200);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
