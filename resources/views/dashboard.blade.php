@extends('layouts.layout')

@section('title', 'ScholarMatch - SPK Matchmaking Beasiswa')

@section('content')
<div class="max-w-6xl mx-auto py-4" x-data="{
    // Form Inputs
    nama_lengkap: '{{ $defaults['nama_lengkap'] }}',
    email: '{{ $defaults['email'] }}',
    semester: {{ $defaults['semester'] }},
    ipk: {{ $defaults['ipk'] }},
    status_akademik: '{{ $defaults['status_akademik'] }}',
    penghasilan_ortu: {{ $defaults['penghasilan_ortu'] }},

    // App state
    showForm: true,
    isLoading: false,
    showResults: false,
    activeTab: 'eligible', // 'eligible' or 'ineligible'
    showMathModal: false,
    loadingStep: 0,

    // API Response data
    user: {},
    recommendations: [], // staggered display list
    allRecommendations: [],
    ineligible: [], // staggered display list
    allIneligible: [],
    premiumScholarships: [],

    formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
    },

    getCountdown(deadlineStr) {
        if (!deadlineStr) return '';
        const diff = new Date(deadlineStr) - new Date();
        if (diff <= 0) return 'Pendaftaran Tutup';
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        if (days > 0) {
            return `Sisa ${days} hari ${hours} jam`;
        }
        return `Sisa ${hours} jam ${minutes} menit`;
    },

    async handleSubmit() {
        this.showForm = false;
        this.isLoading = true;
        this.showResults = false;
        this.loadingStep = 0;
        this.recommendations = [];
        this.ineligible = [];

        try {
            const response = await fetch('{{ route('matchmaking.calculate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nama_lengkap: this.nama_lengkap,
                    email: this.email,
                    semester: parseInt(this.semester),
                    ipk: parseFloat(this.ipk),
                    status_akademik: this.status_akademik,
                    penghasilan_ortu: parseInt(this.penghasilan_ortu)
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.user = data.user;
                this.allRecommendations = data.recommendations;
                this.allIneligible = data.ineligible;
                this.premiumScholarships = data.premium_scholarships;

                // Animate steps sequentially
                this.loadingStep = 1;
                
                setTimeout(() => {
                    this.loadingStep = 2;
                    
                    setTimeout(() => {
                        this.loadingStep = 3;
                        
                        setTimeout(() => {
                            this.isLoading = false;
                            this.showResults = true;

                            // Staggered fade-in for recommendations
                            this.allRecommendations.forEach((rec, index) => {
                                setTimeout(() => {
                                    this.recommendations.push(rec);
                                }, index * 150);
                            });

                            // Staggered fade-in for disqualified
                            this.allIneligible.forEach((sch, index) => {
                                setTimeout(() => {
                                    this.ineligible.push(sch);
                                }, index * 150 + 100);
                            });
                        }, 800);
                    }, 800);
                }, 800);
            } else {
                alert('Gagal memproses matchmaking. Pastikan data valid.');
                this.isLoading = false;
                this.showForm = true;
            }
        } catch (error) {
            console.error(error);
            alert('Kesalahan koneksi internet.');
            this.isLoading = false;
            this.showForm = true;
        }
    },

    async handleVisit(rec) {
        const id = rec.scholarship.id;
        const url = rec.scholarship.url_tautan;

        // Perform AJAX click logging
        try {
            const response = await fetch(`/scholarship/${id}/go`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: this.email
                })
            });
            const data = await response.json();
            if (data.success) {
                // Reactively update click counter
                rec.scholarship.jumlah_klik = data.jumlah_klik;
            }
        } catch(e) {
            console.error(e);
        }

        // Open original URL in a new tab
        window.open(url, '_blank');
    },

    resetForm() {
        this.showResults = false;
        this.showForm = true;
    }
}">

    <!-- ==================== 1. FORM STATE ==================== -->
    <div x-show="showForm" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <!-- Title Section -->
        <div class="text-center mb-10">
            <h1 class="font-title font-black text-4xl sm:text-5xl tracking-tight text-white mb-3">
                Agregator Matchmaking <span class="bg-gradient-to-r from-emerald-400 to-blue-500 bg-clip-text text-transparent">Beasiswa Akurat</span>
            </h1>
            <p class="text-zinc-400 max-w-xl mx-auto text-sm">
                ScholarMatch menggunakan Two-Stage Decision Engine untuk menyaring kualifikasi minimum dan mengkalkulasi beasiswa terbaik yang paling sesuai dengan profil Anda.
            </p>
        </div>

        <!-- Interactive Glassmorphic Form Card -->
        <div class="relative bg-zinc-900/60 backdrop-blur-xl border border-zinc-800/80 rounded-3xl p-8 sm:p-10 shadow-2xl overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-bl from-emerald-500/10 to-blue-500/0 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-blue-600/5 to-emerald-500/0 rounded-full blur-3xl pointer-events-none"></div>

            <form @submit.prevent="handleSubmit" class="relative z-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Nama Lengkap -->
                    <div class="space-y-2">
                        <label for="nama_lengkap" class="block text-sm font-semibold text-zinc-300">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" x-model="nama_lengkap"
                            class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300" required>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-zinc-300">Alamat Email</label>
                        <input type="email" name="email" id="email" x-model="email"
                            class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300" required>
                    </div>

                    <!-- Semester (Slider) -->
                    <div class="space-y-3 bg-zinc-950/40 p-5 rounded-2xl border border-zinc-800/50">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-zinc-300">Semester Aktif</label>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-title" x-text="'Sem ' + semester"></span>
                        </div>
                        <div class="pt-2">
                            <input type="range" name="semester" min="1" max="14" x-model="semester"
                                class="w-full h-1.5 bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-emerald-500 focus:outline-none">
                        </div>
                        <div class="flex justify-between text-[10px] text-zinc-500 font-semibold px-0.5">
                            <span>Sem 1</span>
                            <span>Sem 7</span>
                            <span>Sem 14</span>
                        </div>
                    </div>

                    <!-- IPK Kumulatif (Slider) -->
                    <div class="space-y-3 bg-zinc-950/40 p-5 rounded-2xl border border-zinc-800/50">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-zinc-300">IPK Kumulatif</label>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-400 border border-blue-500/20 font-title" x-text="parseFloat(ipk).toFixed(2)"></span>
                        </div>
                        <div class="pt-2">
                            <input type="range" name="ipk" min="0.0" max="4.0" step="0.01" x-model="ipk"
                                class="w-full h-1.5 bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-blue-500 focus:outline-none">
                        </div>
                        <div class="flex justify-between text-[10px] text-zinc-500 font-semibold px-0.5">
                            <span>0.00</span>
                            <span>2.00</span>
                            <span>4.00</span>
                        </div>
                    </div>

                    <!-- Status Akademik -->
                    <div class="space-y-2">
                        <label for="status_akademik" class="block text-sm font-semibold text-zinc-300">Status Akademik</label>
                        <select name="status_akademik" id="status_akademik" x-model="status_akademik"
                            class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300 appearance-none cursor-pointer">
                            <option value="Aktif Regular">Aktif Regular</option>
                            <option value="Aktif Transfer">Aktif Transfer</option>
                            <option value="Cuti">Cuti Akademik</option>
                        </select>
                    </div>

                    <!-- Penghasilan Orang Tua -->
                    <div class="space-y-2">
                        <label for="penghasilan_ortu" class="block text-sm font-semibold text-zinc-300">Estimasi Penghasilan Orang Tua (Per Bulan)</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-sm font-bold text-zinc-500">Rp</span>
                            <input type="number" name="penghasilan_ortu" id="penghasilan_ortu" x-model.number="penghasilan_ortu"
                                class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl pl-12 pr-4 py-3 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300" min="0" required>
                        </div>
                        <p class="text-xs text-zinc-500 font-medium tracking-wide mt-1" x-text="'Terformat: ' + formatRupiah(penghasilan_ortu)"></p>
                    </div>
                </div>

                <!-- Submit Button & Collapsible Methodology Note -->
                <div class="pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between border-t border-zinc-800/40 gap-4 mt-6">
                    <div x-data="{ open: false }" class="flex-grow">
                        <button type="button" @click="open = !open" class="flex items-center space-x-1.5 text-zinc-400 hover:text-zinc-300 text-xs font-semibold focus:outline-none transition-colors">
                            <span>ℹ️ Mengapa hanya 4 kriteria?</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="mt-3 text-zinc-300 text-[11px] leading-relaxed max-w-2xl text-left font-medium">
                            ScholarMatch menggunakan 4 kriteria inti yang terbukti paling signifikan dalam proses seleksi beasiswa di Indonesia: IPK (bobot 30%), Kesesuaian Semester (25%), Penghasilan Orang Tua (25%), dan Status Akademik (20%). Metodologi ini mengacu pada pendekatan SAW (Simple Additive Weighting) yang divalidasi dalam penelitian sistem pendukung keputusan beasiswa.
                        </div>
                    </div>
                    
                    <button type="submit" class="relative inline-flex items-center justify-center p-0.5 overflow-hidden text-sm font-bold text-white rounded-2xl group bg-gradient-to-br from-emerald-500 via-emerald-400 to-blue-600 w-full sm:w-auto shrink-0 shadow-xl shadow-emerald-500/10 hover:shadow-emerald-500/25 transition-all duration-300">
                        <span class="relative px-8 py-3.5 transition-all ease-in duration-75 bg-zinc-900 rounded-[14px] group-hover:bg-opacity-0 w-full text-center">
                            Mulai Pencocokan
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Horizontal Stepper (Inactive / Grayed Out before clicking) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 max-w-4xl mx-auto py-6 mt-10 border-t border-zinc-800/40">
            <!-- Step 1 -->
            <div class="flex-grow flex flex-col items-center text-center px-4 opacity-60 text-zinc-400">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg border border-zinc-700 bg-zinc-950">
                    🛡️
                </div>
                <span class="text-xs font-bold uppercase tracking-wider mt-3">Tahap 1: Filtering</span>
                <span class="text-[10px] text-zinc-300 mt-1 block font-medium">Menyaring syarat IPK, Penghasilan, dan Batas Waktu</span>
            </div>

            <div class="hidden sm:block w-12 h-0.5 bg-zinc-800"></div>

            <!-- Step 2 -->
            <div class="flex-grow flex flex-col items-center text-center px-4 opacity-60 text-zinc-400">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg border border-zinc-700 bg-zinc-950">
                    📈
                </div>
                <span class="text-xs font-bold uppercase tracking-wider mt-3">Tahap 2: Eligibility Score</span>
                <span class="text-[10px] text-zinc-300 mt-1 block font-medium">Menghitung skor kelayakan IPK dan rasio pendapatan</span>
            </div>

            <div class="hidden sm:block w-12 h-0.5 bg-zinc-800"></div>

            <!-- Step 3 -->
            <div class="flex-grow flex flex-col items-center text-center px-4 opacity-60 text-zinc-400">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg border border-zinc-700 bg-zinc-950">
                    📊
                </div>
                <span class="text-xs font-bold uppercase tracking-wider mt-3">Tahap 3: SAW Ranking</span>
                <span class="text-[10px] text-zinc-300 mt-1 block font-medium">Normalisasi matriks keputusan dan perangkingan SAW</span>
            </div>
        </div>
    </div>


    <!-- ==================== 2. LOADING STATE (STEPPER) ==================== -->
    <div x-show="isLoading" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-8">
        <!-- Stepper Header -->
        <div class="text-center mb-10">
            <h1 class="font-title font-black text-3xl sm:text-4xl tracking-tight text-white mb-2">
                Memproses Pencocokan Beasiswa
            </h1>
            <p class="text-zinc-400 max-w-xl mx-auto text-sm">
                Sistem sedang menjalankan perhitungan Two-Stage Decision Engine secara berurutan...
            </p>
        </div>

        <div class="bg-zinc-900/60 backdrop-blur-xl border border-zinc-800/80 rounded-3xl p-8 sm:p-10 shadow-2xl space-y-8">
            <!-- Animated Stepper -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 max-w-4xl mx-auto py-6">
                <!-- Step 1 -->
                <div class="flex-grow flex flex-col items-center text-center px-4 transition-all duration-500" :class="loadingStep >= 1 ? 'opacity-100' : 'opacity-50 text-zinc-450'">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl border-2 transition-all duration-500" :class="loadingStep >= 1 ? 'bg-emerald-500/10 border-emerald-500 text-emerald-400 shadow-lg shadow-emerald-500/10' : 'bg-zinc-950 border-zinc-800 text-zinc-450'">
                        🛡️
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider mt-4" :class="loadingStep >= 1 ? 'text-emerald-400' : 'text-zinc-300'">Tahap 1: Filtering</span>
                    <span x-show="loadingStep === 1" x-transition class="text-[10px] text-zinc-200 mt-2 block leading-normal font-semibold">
                        Menyaring beasiswa berdasarkan IPK, Penghasilan Orang Tua, dan batas waktu pendaftaran...
                    </span>
                </div>

                <div class="hidden sm:block w-16 h-0.5 transition-all duration-500" :class="loadingStep >= 2 ? 'bg-emerald-500/80' : 'bg-zinc-800'"></div>

                <!-- Step 2 -->
                <div class="flex-grow flex flex-col items-center text-center px-4 transition-all duration-500" :class="loadingStep >= 2 ? 'opacity-100' : 'opacity-50 text-zinc-450'">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl border-2 transition-all duration-500" :class="loadingStep >= 2 ? 'bg-blue-500/10 border-blue-500 text-blue-400 shadow-lg shadow-blue-500/10' : 'bg-zinc-950 border-zinc-800 text-zinc-450'">
                        📈
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider mt-4" :class="loadingStep >= 2 ? 'text-blue-400' : 'text-zinc-300'">Tahap 2: Eligibility Score</span>
                    <span x-show="loadingStep === 2" x-transition class="text-[10px] text-zinc-200 mt-2 block leading-normal font-semibold">
                        Mengonversi data akademik menjadi skor kelayakan (skala 1-5) kriteria C1 - C4...
                    </span>
                </div>

                <div class="hidden sm:block w-16 h-0.5 transition-all duration-500" :class="loadingStep >= 3 ? 'bg-blue-500/80' : 'bg-zinc-800'"></div>

                <!-- Step 3 -->
                <div class="flex-grow flex flex-col items-center text-center px-4 transition-all duration-500" :class="loadingStep >= 3 ? 'opacity-100' : 'opacity-50 text-zinc-450'">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl border-2 transition-all duration-500" :class="loadingStep >= 3 ? 'bg-purple-500/10 border-purple-500 text-purple-400 shadow-lg shadow-purple-500/10' : 'bg-zinc-950 border-zinc-800 text-zinc-450'">
                        📊
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider mt-4" :class="loadingStep >= 3 ? 'text-purple-400' : 'text-zinc-300'">Tahap 3: SAW Ranking</span>
                    <span x-show="loadingStep === 3" x-transition class="text-[10px] text-zinc-200 mt-2 block leading-normal font-semibold">
                        Normalisasi matriks dan penghitungan preferensi akhir Simple Additive Weighting...
                    </span>
                </div>
            </div>

            <!-- Loader spinner line -->
            <div class="text-center text-[10px] text-zinc-500 font-bold uppercase tracking-widest pt-4 flex items-center justify-center space-x-2 border-t border-zinc-800/40">
                <svg class="animate-spin h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loadingStep === 1 ? 'Menjalankan Tahap 1: Filtrasi Kualifikasi...' : (loadingStep === 2 ? 'Menjalankan Tahap 2: Pembobotan Kelayakan...' : 'Menjalankan Tahap 3: Perangkingan SAW...')"></span>
            </div>
        </div>
    </div>


    <!-- ==================== 3. RESULTS STATE ==================== -->
    <div x-show="showResults" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
        <!-- Results Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 border-b border-zinc-800/60 pb-6">
            <div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-title uppercase tracking-widest">
                    Peringkat Rekomendasi
                </span>
                <h1 class="font-title font-black text-3xl sm:text-4xl text-white mt-3">
                    Analisis Beasiswa untuk <span x-text="user.nama_lengkap"></span>
                </h1>
                <p class="text-zinc-500 text-sm mt-1">
                    IPK: <span class="text-zinc-300 font-semibold" x-text="parseFloat(user.ipk).toFixed(2)"></span> &bull; 
                    Semester: <span class="text-zinc-300 font-semibold" x-text="user.semester"></span> &bull; 
                    Status: <span class="text-zinc-300 font-semibold" x-text="user.status_akademik"></span> &bull; 
                    Penghasilan: <span class="text-zinc-300 font-semibold" x-text="formatRupiah(user.penghasilan_ortu) + '/bln'"></span>
                </p>
            </div>
            
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <div class="bg-zinc-900 border border-zinc-800 p-1 rounded-xl flex">
                    <button @click="activeTab = 'eligible'" :class="activeTab === 'eligible' ? 'bg-zinc-800 text-white shadow' : 'text-zinc-400 hover:text-zinc-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all">
                        Beasiswa Layak
                    </button>
                    <button @click="activeTab = 'ineligible'" :class="activeTab === 'ineligible' ? 'bg-zinc-800 text-white shadow border border-red-500/20' : 'text-zinc-400 hover:text-zinc-200'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1">
                        <span>Tidak Memenuhi Syarat</span>
                        <span class="w-4.5 h-4.5 text-[9px] rounded-full bg-red-500/10 border border-red-500/30 text-red-400 flex items-center justify-center font-bold" x-text="allIneligible.length"></span>
                    </button>
                </div>

                <button @click="resetForm()" class="px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-xs font-bold text-zinc-300 hover:bg-zinc-800/80 transition-all flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" /></svg>
                    <span>Cari Ulang</span>
                </button>
                <button @click="showMathModal = true" class="px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-xs font-bold text-emerald-400 hover:bg-zinc-800/80 transition-all flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    <span>Kalkulasi SAW</span>
                </button>
            </div>
        </div>

        <!-- TAB CONTENT: ELIGIBLE RECOMMENDATIONS -->
        <div x-show="activeTab === 'eligible'" class="space-y-12">
            <!-- Bento Grid with Staggered Rendering & Gating Lock -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <template x-for="(rec, index) in recommendations" :key="rec.scholarship.id">
                    
                    <!-- Free Gating Rule Check: If index >= 5 and user is not premium -->
                    <div x-data="{ isGated: index >= 5 && !user.is_premium }" 
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="relative bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-6 shadow-xl flex flex-col justify-between transition-all duration-500 hover:translate-y-[-4px]"
                        :class="isGated ? 'bg-zinc-950/20 border-zinc-900/60 overflow-hidden' : (rec.peringkat === 1 ? 'md:col-span-2 ring-2 ring-emerald-500/30 bg-gradient-to-br from-zinc-900/60 via-zinc-900/40 to-emerald-950/10 hover:border-zinc-700/80' : 'hover:border-zinc-700/80')">

                        <!-- Locked screen overlay for Gated Card -->
                        <div x-show="isGated" class="absolute inset-0 bg-zinc-950/90 backdrop-blur-md z-20 flex flex-col items-center justify-center p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400 mb-2">
                                <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <h4 class="font-title font-bold text-sm text-white">Upgrade ke Premium</h4>
                            <p class="text-[10px] text-zinc-500 max-w-[200px] mt-1 mb-3" x-text="'Membuka ' + (allRecommendations.length - 5) + ' beasiswa relevan lainnya'"></p>
                            <a href="{{ route('premium.index') }}" class="px-4 py-1.5 bg-gradient-to-r from-emerald-500 to-blue-600 text-zinc-950 font-bold rounded-lg text-[10px] transition-all hover:scale-105">
                                Buka Akses
                            </a>
                        </div>

                        <!-- Card Header -->
                        <div :class="isGated ? 'opacity-20 pointer-events-none select-none' : ''">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-1.5">
                                    <span class="w-6 h-6 rounded-lg font-title font-bold text-[10px] flex items-center justify-center border"
                                        :class="rec.peringkat === 1 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-blue-500/10 border-blue-500/30 text-blue-400'">
                                        #<span x-text="rec.peringkat"></span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider border"
                                        :class="rec.peringkat === 1 ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-blue-500/10 border-blue-500/20 text-blue-400'"
                                        x-text="rec.rekomendasi === 'SANGAT DIREKOMENDASIKAN' ? 'Sangat Relevan' : 'Relevan'">
                                    </span>
                                    <span x-show="rec.scholarship.is_featured" class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/10 border border-amber-500/20 text-amber-400">
                                        Featured
                                    </span>
                                </div>

                                <!-- Radial SVG Match Ring / Lock for non-premium -->
                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <template x-if="user.is_premium">
                                        <div class="relative w-full h-full flex items-center justify-center">
                                            <svg class="w-full h-full transform -rotate-90">
                                                <circle cx="24" cy="24" r="20" stroke-width="3" stroke="#1f2937" fill="transparent" />
                                                <circle cx="24" cy="24" r="20" stroke-width="3" 
                                                    :stroke="rec.peringkat === 1 ? '#10b981' : '#3b82f6'" 
                                                    stroke-dasharray="125.6" 
                                                    :stroke-dashoffset="125.6 - (125.6 * rec.skor_persen / 100)" 
                                                    stroke-linecap="round" fill="transparent" />
                                            </svg>
                                            <span class="absolute text-[10px] font-black font-title text-white" x-text="rec.skor_persen + '%'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!user.is_premium">
                                        <div class="w-10 h-10 rounded-full bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-500 font-bold" title="Upgrade Premium untuk membuka skor relevansi">
                                            🔒
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <h3 class="font-title font-black text-lg text-zinc-100 mt-2 mb-1" x-text="rec.scholarship.nama_beasiswa"></h3>
                            <div class="flex items-center space-x-2 text-[10px] font-bold text-zinc-500 tracking-wide uppercase mb-3">
                                <span x-text="rec.scholarship.penyelenggara"></span>
                            </div>

                            <p class="text-zinc-400 text-xs leading-relaxed mb-6" x-text="rec.scholarship.deskripsi"></p>
                        </div>

                        <!-- Card Footer -->
                        <div class="border-t border-zinc-800/80 pt-4 mt-auto flex flex-wrap items-center justify-between gap-4"
                            :class="isGated ? 'opacity-20 pointer-events-none select-none' : ''">
                            
                            <!-- Static parameters & tracking counter -->
                            <div class="flex flex-col space-y-2 text-[10px] text-zinc-500">
                                <div class="flex items-center space-x-3">
                                    <div>
                                        <span class="text-zinc-400 font-bold" x-text="'Sem ' + rec.scholarship.semester_min + '-' + rec.scholarship.semester_max"></span>
                                        <span class="text-[9px] block text-zinc-600 uppercase">Syarat Sem</span>
                                    </div>
                                    <div class="w-px h-6 bg-zinc-800"></div>
                                    <div>
                                        <span class="text-zinc-400 font-bold" x-text="getCountdown(rec.scholarship.batas_waktu)"></span>
                                        <span class="text-[9px] block text-zinc-650 uppercase font-medium">Batas Waktu</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1 text-emerald-400/90 font-medium">
                                    <span>🔥</span>
                                    <span x-text="rec.scholarship.jumlah_klik + ' Mahasiswa mengakses'"></span>
                                </div>
                            </div>

                            <button @click="handleVisit(rec)"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5"
                                :class="rec.peringkat === 1 ? 'bg-emerald-500 text-zinc-950 hover:bg-emerald-400' : 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700'">
                                <span>Daftar</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </button>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <!-- TAB CONTENT: INELIGIBLE DISQUALIFIED -->
        <div x-show="activeTab === 'ineligible'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <template x-for="sch in ineligible" :key="sch.id">
                    <div x-transition:enter="transition ease-out duration-500"
                        class="bg-zinc-950/40 border border-zinc-900 rounded-2xl p-5 flex flex-col justify-between opacity-55 relative group hover:border-red-950/40">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-red-500/10 border border-red-500/20 text-red-400">
                                    Gugur Tahap 1
                                </span>
                                <span class="text-[10px] font-medium text-zinc-500" x-text="getCountdown(sch.batas_waktu)"></span>
                            </div>
                            <h4 class="font-title font-bold text-base text-zinc-300 mb-1" x-text="sch.nama_beasiswa"></h4>
                            <p class="text-[10px] text-zinc-650 font-bold uppercase tracking-wider mb-3" x-text="sch.penyelenggara"></p>
                            
                            <!-- Expiration / Disqualified Reason -->
                            <div class="mt-4 p-3 bg-red-500/5 border border-red-950/30 rounded-xl flex items-start space-x-2">
                                <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <span class="block text-[9px] uppercase font-bold text-red-400 tracking-wider">Alasan Eliminasi</span>
                                    <span class="text-xs text-red-300/80 leading-normal" x-text="sch.gugur_reasons"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <div x-show="ineligible.length === 0" class="text-center py-12 text-zinc-500 text-sm">
                Tidak ada alternatif beasiswa yang gugur untuk profil ini. Semua lolos tahap filtrasi!
            </div>
        </div>

    </div>

    <!-- ==================== 4. KALKULASI SAW MODAL ==================== -->
    <div x-show="showMathModal" class="fixed inset-0 z-50 overflow-y-auto" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="showMathModal = false"></div>
            
            <div class="relative bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 w-full max-w-4xl shadow-2xl z-10 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center border-b border-zinc-800 pb-3">
                    <h3 class="font-title font-bold text-lg text-white">Rumus & Langkah Perhitungan SAW</h3>
                    <button @click="showMathModal = false" class="text-zinc-500 hover:text-zinc-300 text-xl font-bold">&times;</button>
                </div>

                <!-- Math Modal Content with Premium Lock -->
                <div class="relative min-h-[300px]">
                    <template x-if="!user.is_premium">
                        <div class="absolute inset-0 bg-zinc-950/95 backdrop-blur-md z-30 flex flex-col items-center justify-center p-6 text-center rounded-2xl border border-zinc-800">
                            <div class="w-12 h-12 rounded-full bg-amber-500/10 border border-amber-500/25 flex items-center justify-center text-amber-400 mb-4 animate-bounce">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <h4 class="font-title font-black text-lg text-white mb-2">Akses Terkunci: Normalisasi & Perhitungan SAW</h4>
                            <p class="text-xs text-zinc-400 max-w-md mb-6 leading-relaxed">
                                Dapatkan transparansi rumus lengkap, matriks keputusan ternormalisasi ($r_{ij}$), dan perhitungan nilai preferensi ($V_i$) akhir dengan meningkatkan ke akun Premium.
                            </p>
                            <a href="{{ route('premium.index') }}" class="px-6 py-2.5 bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-350 hover:to-orange-450 text-zinc-950 font-bold rounded-xl text-xs transition-all hover:scale-105 shadow-lg shadow-amber-500/15">
                                Upgrade Ke Premium Sekarang
                            </a>
                        </div>
                    </template>

                    <div :class="!user.is_premium ? 'filter blur-md select-none pointer-events-none' : ''" class="space-y-6 text-xs text-zinc-300 leading-relaxed">
                        <div>
                            <h4 class="font-semibold text-white mb-2">1. Formula Utama Simple Additive Weighting (SAW)</h4>
                            <p class="bg-zinc-950 p-4 rounded-xl font-mono text-center text-sm border border-zinc-800 text-emerald-400">
                                Vi = &Sigma; (Wj &times; rij)
                            </p>
                            <p class="mt-2 text-zinc-400">
                                Dimana <strong>Vi</strong> adalah nilai preferensi akhir alternatif ke-i, <strong>Wj</strong> adalah bobot kriteria ke-j, dan <strong>rij</strong> adalah matriks keputusan ternormalisasi.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-white mb-2">2. Prosedur Normalisasi (Semua Kriteria = Benefit)</h4>
                            <p class="bg-zinc-950 p-4 rounded-xl font-mono text-center text-sm border border-zinc-800 text-blue-400">
                                rij = xij / Max(xij)
                            </p>
                            <p class="mt-2 text-zinc-400">
                                Karena C1 hingga C4 bertipe <strong>Benefit</strong> (semakin besar semakin layak), nilai ternormalisasi didapat dengan membagi nilai kriteria asli ($x_{ij}$) dengan nilai terbesar pada kolom tersebut ($Max(x_{ij})$).
                            </p>
                        </div>

                        <!-- Matriks Keputusan Asli -->
                        <div class="overflow-x-auto rounded-xl border border-zinc-800/80 bg-zinc-950/40 p-4">
                            <h4 class="text-xs text-zinc-500 font-bold uppercase tracking-wider mb-2">3. Matriks Keputusan Asli ($x_{ij}$)</h4>
                            <table class="w-full text-left border-collapse text-xs text-zinc-300">
                                <thead>
                                    <tr class="border-b border-zinc-800 text-zinc-500 font-bold">
                                        <th class="py-2">Alternatif Beasiswa</th>
                                        <th class="py-2 text-center">C1 (Rentang Sem)</th>
                                        <th class="py-2 text-center">C2 (Skor Status)</th>
                                        <th class="py-2 text-center">C3 (Skor IPK)</th>
                                        <th class="py-2 text-center">C4 (Skor Pendap.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="rec in allRecommendations" :key="'modal-raw-' + rec.scholarship.id">
                                        <tr class="border-b border-zinc-800/30">
                                            <td class="py-2 font-semibold text-zinc-200" x-text="rec.scholarship.nama_beasiswa.split(':')[0]"></td>
                                            <td class="py-2 text-center" x-text="rec.raw_values.C1"></td>
                                            <td class="py-2 text-center" x-text="rec.raw_values.C2"></td>
                                            <td class="py-2 text-center" x-text="rec.raw_values.C3"></td>
                                            <td class="py-2 text-center" x-text="rec.raw_values.C4"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Matriks Ternormalisasi -->
                        <div class="overflow-x-auto rounded-xl border border-zinc-800/80 bg-zinc-950/40 p-4">
                            <h4 class="text-xs text-zinc-500 font-bold uppercase tracking-wider mb-2">4. Matriks Ternormalisasi ($r_{ij}$) & Nilai Preferensi ($V_i$)</h4>
                            <table class="w-full text-left border-collapse text-xs text-zinc-300">
                                <thead>
                                    <tr class="border-b border-zinc-800 text-zinc-500 font-bold">
                                        <th class="py-2">Alternatif</th>
                                        <th class="py-2 text-center">C1 (W: 0.25)</th>
                                        <th class="py-2 text-center">C2 (W: 0.20)</th>
                                        <th class="py-2 text-center">C3 (W: 0.30)</th>
                                        <th class="py-2 text-center">C4 (W: 0.25)</th>
                                        <th class="py-2 text-right">Skor Akhir ($V_i$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="rec in allRecommendations" :key="'modal-norm-' + rec.scholarship.id">
                                        <tr class="border-b border-zinc-800/30">
                                            <td class="py-2 font-semibold text-zinc-200" x-text="rec.scholarship.nama_beasiswa.split(':')[0]"></td>
                                            <td class="py-2 text-center" x-text="parseFloat(rec.normalized_values.C1).toFixed(3)"></td>
                                            <td class="py-2 text-center" x-text="parseFloat(rec.normalized_values.C2).toFixed(3)"></td>
                                            <td class="py-2 text-center" x-text="parseFloat(rec.normalized_values.C3).toFixed(3)"></td>
                                            <td class="py-2 text-center" x-text="parseFloat(rec.normalized_values.C4).toFixed(3)"></td>
                                            <td class="py-2 text-right font-bold text-emerald-400 font-title" x-text="parseFloat(rec.nilai_preferensi).toFixed(4)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-zinc-800">
                    <button @click="showMathModal = false" class="px-5 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold rounded-xl text-xs transition-all">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
