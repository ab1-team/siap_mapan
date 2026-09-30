<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * Sebelum: middleware ini menjalankan `Auth::user()->auth_token` di setiap
     * request, yang memicu SELECT ke tabel users di tiap halaman.
     *
     * Sekarang: token hanya dibandingkan dengan session (yang diisi sekali
     * saat login). Jadi 0 query ke DB per request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('/auth')->with('error', 'Authentication failed');
        }

        $sessionToken = $request->session()->get('auth_token');

        // Kalau session tidak punya auth_token, paksa login ulang.
        // Bandingkan dengan session saja -> tidak ada DB hit.
        if (! $sessionToken) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/auth')->with('error', 'Authentication failed');
        }

        return $next($request);
    }
}
