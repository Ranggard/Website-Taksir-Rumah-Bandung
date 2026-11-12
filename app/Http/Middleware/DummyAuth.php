<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Pengganti sementara untuk auth Laravel (front end saja, tanpa database).
 * Status login hanya disimpan di session('user').
 * Nanti ganti dengan middleware bawaan: 'auth' dan 'guest'.
 */
class DummyAuth
{
    public function handle(Request $request, Closure $next, string $mode = 'auth')
    {
        $login = $request->session()->has('user');

        if ($mode === 'auth' && !$login) {
            if ($request->isMethod('get')) {
                $request->session()->put('intended', $request->fullUrl());
            }
            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu untuk memakai fitur ini.');
        }

        if ($mode === 'guest' && $login) {
            return redirect()->route('home');
        }

        $response = $next($request);
        // Supaya tombol Back setelah logout tidak menampilkan halaman terlindungi
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        return $response;
    }
}
