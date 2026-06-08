<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login sistem.
     */
    public function showLogin()
    {
        // Jika user sudah masuk, langsung arahkan ke dashboard matchmaking
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Jalankan proses login / verifikasi kredensial.
     */
    public function login(Request $request)
    {
        // Validasi input email dan sandi
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Verifikasi kecocokan kredensial
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Perbarui waktu terakhir aktif user saat login sukses
            $user->last_active_at = now();
            $user->save();

            // Jika user bertindak sebagai admin, arahkan ke panel admin
            if ($user->is_admin) {
                return redirect()->route('admin.scholarships.index');
            }

            return redirect()->route('dashboard');
        }

        // Kembalikan ke halaman login apabila verifikasi gagal
        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    /**
     * Hancurkan sesi login (logout) user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
