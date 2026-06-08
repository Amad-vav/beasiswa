<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Jalankan filter validasi status administrator pada rute khusus admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tolak akses jika user belum login atau bukan merupakan admin
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Aksi tidak diizinkan. Halaman ini hanya untuk Administrator.');
        }

        return $next($request);
    }
}
