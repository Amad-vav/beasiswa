<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Jalankan perekaman jejak waktu aktivitas user (last_active_at) pada setiap request web.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user telah login, perbarui waktu aktivitas terakhirnya
        if (auth()->check()) {
            $user = auth()->user();
            $user->last_active_at = now();
            $user->save();
        }

        return $next($request);
    }
}
