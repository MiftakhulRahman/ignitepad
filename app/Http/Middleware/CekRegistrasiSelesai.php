<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekRegistrasiSelesai
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika user login TAPI belum selesai registrasi
        // DAN user sedang TIDAK berada di halaman 'lengkapi-profil' atau 'logout'
        if ($user && !$user->registrasi_selesai && 
            !$request->routeIs('onboarding.*') && 
            !$request->routeIs('logout')) {
            
            return redirect()->route('onboarding.form');
        }

        return $next($request);
    }
}