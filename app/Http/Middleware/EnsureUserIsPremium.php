<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPremium
{
    /**
     * Jalankan filter validasi status keanggotaan premium pada rute katalog.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tolak akses jika user belum login atau bukan merupakan user premium
        if (!auth()->check() || !auth()->user()->is_premium) {
            return redirect()->route('premium.index')->with('error', 'Halaman Katalog Beasiswa hanya dapat diakses oleh akun Premium.');
        }

        return $next($request);
    }
}
