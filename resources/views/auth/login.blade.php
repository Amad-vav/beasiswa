@extends('layouts.layout')

@section('title', 'Login - ScholarMatch')

@section('content')
<div class="max-w-md mx-auto py-8" x-data="{
    email: '',
    password: '',
    quickLogin(email, password) {
        this.email = email;
        this.password = password;
        // Small delay to let Alpine render inputs before submitting
        $nextTick(() => {
            $refs.loginForm.submit();
        });
    }
}">
    <!-- Title Section -->
    <div class="text-center mb-8">
        <h1 class="font-title font-black text-3xl tracking-tight text-white mb-2">
            Selamat Datang di <span class="bg-gradient-to-r from-emerald-400 to-blue-500 bg-clip-text text-transparent">ScholarMatch</span>
        </h1>
        <p class="text-zinc-400 text-sm">
            Silakan masuk untuk menggunakan SPK Matchmaking Beasiswa
        </p>
    </div>

    <!-- Glassmorphic Login Card -->
    <div class="relative bg-zinc-900/60 backdrop-blur-xl border border-zinc-800/80 rounded-3xl p-8 shadow-2xl overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-emerald-500/10 to-blue-500/0 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-600/5 to-emerald-500/0 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Session Status / Errors -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-xl flex items-start space-x-2 animate-pulse">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <span class="font-bold block">Gagal Masuk</span>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form x-ref="loginForm" action="{{ route('login') }}" method="POST" class="relative z-10 space-y-6">
            @csrf

            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-zinc-300">Alamat Email</label>
                <input type="email" name="email" id="email" x-model="email"
                    class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300" required>
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="block text-sm font-semibold text-zinc-300">Password</label>
                <input type="password" name="password" id="password" x-model="password"
                    class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300" required>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded bg-zinc-950 border-zinc-800 text-emerald-500 focus:ring-emerald-500/20 focus:ring-offset-zinc-900">
                <label for="remember_me" class="ml-2 block text-xs font-semibold text-zinc-400">Ingat Saya</label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="relative inline-flex items-center justify-center p-0.5 overflow-hidden text-sm font-bold text-white rounded-2xl group bg-gradient-to-br from-emerald-500 via-emerald-400 to-blue-600 w-full shadow-xl shadow-emerald-500/10 hover:shadow-emerald-500/25 transition-all duration-300">
                <span class="relative px-8 py-3 transition-all ease-in duration-75 bg-zinc-900 rounded-[14px] group-hover:bg-opacity-0 w-full text-center">
                    Masuk ke Akun
                </span>
            </button>
        </form>

        <!-- Quick Login Helper -->
        <div class="mt-8 pt-6 border-t border-zinc-800/80">
            <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-widest text-center mb-4">Akses Cepat Pengujian (1-Click Login)</h4>
            <div class="space-y-3">
                <!-- Andi (Regular) -->
                <button type="button" @click="quickLogin('andi@example.com', 'password123')"
                    class="w-full p-3 bg-zinc-950/60 hover:bg-zinc-900 border border-zinc-800/80 hover:border-zinc-700 rounded-xl text-left flex items-center justify-between transition-all duration-200 group">
                    <div>
                        <span class="text-xs font-bold text-zinc-200 block group-hover:text-emerald-400 transition-colors">Andi Pratama</span>
                        <span class="text-[10px] text-zinc-500">andi@example.com</span>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-zinc-500/10 text-zinc-400 text-[9px] border border-zinc-500/20 font-bold uppercase">Reguler</span>
                </button>

                <!-- Budi (Premium) -->
                <button type="button" @click="quickLogin('budi@example.com', 'password123')"
                    class="w-full p-3 bg-zinc-950/60 hover:bg-zinc-900 border border-zinc-800/80 hover:border-zinc-700 rounded-xl text-left flex items-center justify-between transition-all duration-200 group">
                    <div>
                        <span class="text-xs font-bold text-zinc-200 block group-hover:text-amber-400 transition-colors">Budi Premium</span>
                        <span class="text-[10px] text-zinc-500">budi@example.com</span>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 text-[9px] border border-amber-500/20 font-bold uppercase">Premium</span>
                </button>

                <!-- Admin -->
                <button type="button" @click="quickLogin('admin@example.com', 'password123')"
                    class="w-full p-3 bg-zinc-950/60 hover:bg-zinc-900 border border-zinc-800/80 hover:border-zinc-700 rounded-xl text-left flex items-center justify-between transition-all duration-200 group">
                    <div>
                        <span class="text-xs font-bold text-zinc-200 block group-hover:text-blue-400 transition-colors">Admin ScholarMatch</span>
                        <span class="text-[10px] text-zinc-500">admin@example.com</span>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-[9px] border border-blue-500/20 font-bold uppercase">Admin</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
