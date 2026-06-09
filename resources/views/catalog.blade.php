@extends('layouts.layout')

@section('title', 'Katalog Beasiswa - ScholarMatch')

@section('content')
<div class="max-w-6xl mx-auto py-4" x-data="{
    search: '{{ request('search') }}',
    min_ipk: '{{ request('min_ipk') }}'
}">
    <!-- Header -->
    <div class="text-center mb-10">
        <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-title uppercase tracking-widest">
            Katalog Alternatif
        </span>
        <h1 class="font-title font-black text-4xl sm:text-5xl tracking-tight text-white mt-3 mb-3">
            Eksplorasi <span class="bg-gradient-to-r from-emerald-400 to-blue-500 bg-clip-text text-transparent">Beasiswa Aktif</span>
        </h1>
        <p class="text-zinc-400 max-w-xl mx-auto text-sm">
            Cari dan telusuri seluruh beasiswa yang terdaftar di sistem ScholarMatch berdasarkan kualifikasi akademik Anda.
        </p>
    </div>

    @if(!auth()->user()->is_premium)
        <!-- Non-intrusive banner for regular users -->
        <div class="mb-8 p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div class="flex items-center space-x-2.5 text-amber-400 text-sm font-semibold justify-center sm:justify-start">
                <span class="text-lg">⚠️</span>
                <span>Anda melihat katalog versi terbatas. Upgrade Premium untuk akses penuh & skor SAW.</span>
            </div>
            <a href="{{ route('premium.index') }}" class="shrink-0 px-4 py-2 bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-350 hover:to-orange-400 text-zinc-950 font-bold rounded-xl text-xs hover:scale-105 transition-transform duration-300">
                Lihat Keuntungan Premium →
            </a>
        </div>
    @endif

    <!-- Search & Filters Bar -->
    <div class="bg-zinc-900/60 backdrop-blur-xl border border-zinc-800/80 rounded-3xl p-6 shadow-xl mb-10">
        <form action="{{ route('scholarships.catalog') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Search Name -->
            <div class="flex-grow space-y-2 w-full">
                <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Cari Beasiswa / Penyelenggara</label>
                <input type="text" name="search" x-model="search" placeholder="Masukkan nama beasiswa, penyelenggara, atau deskripsi..."
                    class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-sm text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300">
            </div>

            <!-- Filter Min IPK -->
            <div class="space-y-2 w-full md:w-48">
                <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Maks Syarat IPK</label>
                <select name="min_ipk" x-model="min_ipk"
                    class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-sm text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300 appearance-none cursor-pointer">
                    <option value="">Semua IPK</option>
                    <option value="2.50">&le; 2.50</option>
                    <option value="3.00">&le; 3.00</option>
                    <option value="3.25">&le; 3.25</option>
                    <option value="3.50">&le; 3.50</option>
                </select>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-blue-600 hover:from-emerald-400 hover:to-blue-500 text-zinc-950 font-bold rounded-xl text-sm transition-all flex items-center justify-center space-x-2 flex-grow md:flex-grow-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <span>Cari</span>
                </button>
                <a href="{{ route('scholarships.catalog') }}" class="px-5 py-3 bg-zinc-950/60 border border-zinc-800 hover:bg-zinc-800 hover:border-zinc-700 text-zinc-300 font-bold rounded-xl text-sm transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Catalog Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($scholarships as $index => $sch)
            @php
                $isGated = !auth()->user()->is_premium && ($index >= 5 || $sch->is_featured);
            @endphp
            <div class="relative bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-6 shadow-xl flex flex-col justify-between transition-all duration-300 hover:translate-y-[-4px] hover:border-zinc-700 {{ $isGated ? 'overflow-hidden' : '' }}">
                @if($isGated)
                    <!-- Locked screen overlay for Gated Card -->
                    <div class="absolute inset-0 bg-zinc-950/90 backdrop-blur-sm z-20 flex flex-col items-center justify-center p-4 text-center">
                        <div class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/25 flex items-center justify-center text-amber-400 mb-2">
                            <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <h4 class="font-title font-bold text-sm text-white">Upgrade ke Premium</h4>
                        <p class="text-[10px] text-zinc-400 max-w-[200px] mt-1 mb-3">
                            {{ $sch->is_featured ? 'Membuka Akses Beasiswa Sponsor Utama B2B' : 'Membuka Akses Katalog Urutan ke-6 dan Seterusnya' }}
                        </p>
                        <a href="{{ route('premium.index') }}" class="px-4 py-1.5 bg-gradient-to-r from-amber-400 to-orange-500 text-zinc-950 font-bold rounded-lg text-[10px] transition-all hover:scale-105">
                            Buka Akses
                        </a>
                    </div>
                @endif

                <!-- Content container (blurred if gated) -->
                <div class="flex-grow flex flex-col justify-between {{ $isGated ? 'filter blur-sm select-none pointer-events-none' : '' }}">
                    <!-- Header info -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider {{ $sch->is_featured ? 'bg-amber-500/10 border border-amber-500/20 text-amber-400' : 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' }}">
                                {{ $sch->is_featured ? 'Featured' : 'Active' }}
                            </span>
                            <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">
                                🔥 {{ $sch->jumlah_klik }} klik
                            </span>
                        </div>

                        <h3 class="font-title font-black text-lg text-zinc-100 mt-2 mb-1">{{ $sch->nama_beasiswa }}</h3>
                        <div class="text-[10px] font-bold text-zinc-500 tracking-wide uppercase mb-3">{{ $sch->penyelenggara }}</div>
                        <p class="text-zinc-400 text-xs leading-relaxed mb-6">{{ $sch->deskripsi }}</p>
                    </div>

                    <!-- Details and CTA -->
                    <div class="border-t border-zinc-800/80 pt-4 mt-auto flex flex-col space-y-4">
                        <!-- Grid specs -->
                        <div class="grid grid-cols-2 gap-2 text-[10px] text-zinc-400">
                            <div>
                                <span class="block text-zinc-650 uppercase font-bold text-[8px]">Syarat IPK</span>
                                <span class="font-semibold text-zinc-300">&ge; {{ number_format($sch->ipk_minimum, 2) }}</span>
                            </div>
                            <div>
                                <span class="block text-zinc-650 uppercase font-bold text-[8px]">Maks Pendapatan</span>
                                <span class="font-semibold text-zinc-300">Rp {{ number_format($sch->batas_penghasilan, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-zinc-650 uppercase font-bold text-[8px]">Sasar Semester</span>
                                <span class="font-semibold text-zinc-300">Sem {{ $sch->semester_min }} - {{ $sch->semester_max }}</span>
                            </div>
                            <div>
                                <span class="block text-zinc-650 uppercase font-bold text-[8px]">Batas Waktu</span>
                                <span class="font-semibold text-zinc-300">{{ $sch->batas_waktu ? $sch->batas_waktu->format('d-m-Y') : 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- CTA -->
                        <a href="{{ $sch->url_tautan }}" target="_blank"
                            class="w-full py-2.5 rounded-xl text-xs font-bold text-center transition-all bg-zinc-800 hover:bg-zinc-700 text-zinc-200 flex items-center justify-center space-x-1 border border-zinc-700/50">
                            <span>Kunjungi Link Resmi</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 text-zinc-500 bg-zinc-900/10 border border-zinc-800 rounded-3xl">
                Tidak menemukan beasiswa aktif yang cocok dengan kriteria pencarian Anda.
            </div>
        @endforelse
    </div>
</div>
@endsection
