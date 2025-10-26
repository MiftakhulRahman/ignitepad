<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah user sudah login DAN rolenya ada di dalam daftar $roles
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {

            // Jika tidak, lempar ke halaman 403 (Forbidden)
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        return $next($request);
    }
}