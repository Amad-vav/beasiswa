@extends('layouts.layout')

@section('title', 'Premium Plan - ScholarMatch')

@section('content')
<div class="max-w-5xl mx-auto py-4">
    <!-- Header -->
    <div class="text-center mb-16">
        <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-title uppercase tracking-widest">
            Pricing Plans
        </span>
        <h1 class="font-title font-black text-4xl sm:text-5xl tracking-tight text-white mt-3 mb-3">
            Tingkatkan Relevansi <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Beasiswa Anda</span>
        </h1>
        <p class="text-zinc-400 max-w-xl mx-auto text-sm">
            Dapatkan hasil pencocokan SPK lengkap tanpa batasan filter dan lihat analisis matriks keputusan yang terperinci.
        </p>
    </div>

    <!-- Pricing Comparison Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch max-w-4xl mx-auto">
        <!-- Free / Regular Plan -->
        <div class="bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-8 flex flex-col justify-between relative">
            <div>
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block mb-2">FREE ACCESS</span>
                <h3 class="font-title font-black text-2xl text-zinc-200 mb-1">Reguler</h3>
                <p class="text-zinc-500 text-xs mb-6">Cocok untuk penelusuran kualifikasi dasar.</p>
                <div class="font-title font-black text-3xl text-white mb-6">Rp 0 <span class="text-xs font-semibold text-zinc-500">/ selamanya</span></div>

                <ul class="space-y-4 text-xs text-zinc-400 mb-8 border-t border-zinc-800/80 pt-6">
                    <li class="flex items-center space-x-2.5">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Two-Stage SPK Matchmaking</span>
                    </li>
                    <li class="flex items-center space-x-2.5">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Melihat 5 Beasiswa Relevan Teratas</span>
                    </li>
                    <li class="flex items-center space-x-2.5">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Detail Log Matriks Normalisasi</span>
                    </li>
                    <li class="flex items-center space-x-2.5 text-zinc-650">
                        <span class="text-zinc-700 font-bold">✗</span>
                        <span>Membuka Peringkat 6 ke Atas (Buram)</span>
                    </li>
                    <li class="flex items-center space-x-2.5 text-zinc-650">
                        <span class="text-zinc-700 font-bold">✗</span>
                        <span>Featured B2B Sponsor Scholarships Gating</span>
                    </li>
                </ul>
            </div>
            
            @if(auth()->user()->is_premium)
                <button disabled class="w-full py-3 rounded-xl bg-zinc-800/40 text-zinc-650 font-bold text-xs cursor-not-allowed">
                    Reguler Plan
                </button>
            @else
                <button disabled class="w-full py-3 rounded-xl bg-zinc-800 text-zinc-500 font-bold text-xs cursor-not-allowed">
                    Plan Aktif
                </button>
            @endif
        </div>

        <!-- Premium Plan -->
        <div class="bg-zinc-900/60 backdrop-blur-md border-2 border-amber-500/30 rounded-3xl p-8 flex flex-col justify-between relative shadow-2xl shadow-amber-500/5">
            <!-- Recommended badge overlay -->
            <div class="absolute top-0 right-8 -translate-y-1/2 bg-gradient-to-r from-amber-500 to-orange-500 text-zinc-950 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg shadow-amber-500/20">
                RECOMMENDED
            </div>

            <div>
                <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest block mb-2">FULL PASS ACCESS</span>
                <h3 class="font-title font-black text-2xl text-white mb-1">Premium Plan</h3>
                <p class="text-zinc-400 text-xs mb-4">Membuka seluruh potensi rekomendasi beasiswa Anda.</p>
                <div class="text-[10px] font-bold text-amber-400 flex items-center gap-1 mb-2 bg-amber-500/10 border border-amber-500/25 px-2.5 py-1 rounded-lg w-max">
                    🔥 Terbatas untuk 100 pengguna pertama
                </div>
                <div class="text-xs text-zinc-500 font-semibold mb-1 tracking-wide">
                    <s>Rp 750.000</s> → Rp 300.000
                </div>
                <div class="font-title font-black text-2xl text-amber-400 mb-6 leading-normal">
                    Rp 300.000 — Akses Seumur Hidup (Harga Early Adopter)
                </div>

                <ul class="space-y-4 text-xs text-zinc-300 mb-8 border-t border-zinc-800/80 pt-6">
                    <li class="flex items-center space-x-2.5">
                        <span class="text-amber-400 font-bold">✓</span>
                        <span>Two-Stage SPK Matchmaking Lengkap</span>
                    </li>
                    <li class="flex items-center space-x-2.5">
                        <span class="text-amber-400 font-bold">✓</span>
                        <span>Akses Tanpa Batas Rekomendasi (Tampil Semua)</span>
                    </li>
                    <li class="flex items-center space-x-2.5">
                        <span class="text-amber-400 font-bold">✓</span>
                        <span>Detail Log Matriks Normalisasi Terbuka</span>
                    </li>
                    <li class="flex items-center space-x-2.5">
                        <span class="text-amber-400 font-bold">✓</span>
                        <span>Membuka Akses Pendaftaran Peringkat 6 ke Atas</span>
                    </li>
                    <li class="flex items-center space-x-2.5">
                        <span class="text-amber-400 font-bold">✓</span>
                        <span>Akses Beasiswa Unggulan Featured Sponsor B2B</span>
                    </li>
                </ul>
            </div>

            @if(auth()->user()->is_premium)
                <div class="w-full py-3 rounded-xl bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold text-xs text-center font-title uppercase tracking-wide">
                    ✓ Akun Premium Aktif
                </div>
            @else
                <div class="space-y-3">
                    <button type="button" disabled class="w-full py-3.5 rounded-xl bg-zinc-800 border border-zinc-700 text-zinc-500 font-bold text-xs uppercase tracking-wider cursor-not-allowed text-center">
                        Pendaftaran Dinonaktifkan
                    </button>
                    <p class="text-[9px] text-zinc-500 text-center leading-normal italic">
                        Tombol dinonaktifkan: Fitur Premium hanya contoh demonstrasi fungsional SPK. Akun Reguler tetap bersifat reguler selamanya.
                    </p>
                </div>
            @endif
            <div class="mt-4 border-t border-zinc-800/80 pt-4 flex flex-col sm:flex-row items-center justify-center gap-x-4 gap-y-1 text-[10px] text-zinc-400 font-medium text-center">
                <span>✓ Tidak ada biaya berulang</span>
                <span class="hidden sm:inline text-zinc-700">•</span>
                <span>✓ Akses permanen</span>
                <span class="hidden sm:inline text-zinc-700">•</span>
                <span>✓ Garansi relevansi SPK</span>
            </div>
        </div>
    </div>
</div>
@endsection
