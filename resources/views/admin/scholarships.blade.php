@extends('layouts.layout')

@section('title', 'Admin Dashboard - ScholarMatch')

@section('content')
<div class="space-y-10" x-data="{
    showAddModal: false,
    showEditModal: false,
    editData: {
        id: '',
        nama_beasiswa: '',
        url_tautan: '',
        penyelenggara: '',
        deskripsi: '',
        semester_min: 1,
        semester_max: 8,
        ipk_minimum: '3.00',
        batas_penghasilan: 3000000,
        skor_status_c2: 3,
        batas_waktu: '',
        is_featured: false,
        status_aktif: true
    },
    // Populate dynamic weights from Laravel
    weights: {
        @foreach($currentWeights as $cw)
            '{{ $cw->id }}': {{ $cw->bobot }},
        @endforeach
    },
    get totalWeight() {
        let sum = 0;
        for (let key in this.weights) {
            sum += parseFloat(this.weights[key] || 0);
        }
        return parseFloat(sum.toFixed(4));
    },
    openEdit(sch) {
        this.editData = {
            id: sch.id,
            nama_beasiswa: sch.nama_beasiswa,
            url_tautan: sch.url_tautan,
            penyelenggara: sch.penyelenggara,
            deskripsi: sch.deskripsi || '',
            semester_min: sch.semester_min,
            semester_max: sch.semester_max,
            ipk_minimum: parseFloat(sch.ipk_minimum).toFixed(2),
            batas_penghasilan: sch.batas_penghasilan,
            skor_status_c2: sch.skor_status_c2,
            batas_waktu: sch.formatted_date,
            is_featured: sch.is_featured === 1 || sch.is_featured === true,
            status_aktif: sch.status_aktif === 1 || sch.status_aktif === true
        };
        this.showEditModal = true;
    }
}">



    <!-- Main Admin Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-zinc-800 pb-6 gap-4">
        <div>
            <h1 class="font-title font-black text-3xl text-white">Panel Administrasi Beasiswa</h1>
            <p class="text-zinc-500 text-xs mt-1">Kelola katalog beasiswa, log statistik klik mahasiswa, dan konfigurasi bobot kriteria SPK.</p>
        </div>
        <button @click="showAddModal = true" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-blue-600 hover:from-emerald-400 hover:to-blue-500 text-zinc-950 font-bold rounded-xl text-xs transition-all flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            <span>Tambah Beasiswa</span>
        </button>
    </div>

    <!-- Layout Grid: Left CRUD Table (2/3), Right Weighting Editor (1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Scholarships CRUD Table -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-6 shadow-xl">
                <h3 class="font-title font-bold text-lg text-white mb-4">Katalog Beasiswa (Total: {{ $scholarships->count() }})</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs text-zinc-300">
                        <thead>
                            <tr class="border-b border-zinc-800 text-zinc-500 font-bold">
                                <th class="py-3 px-2">Nama Beasiswa / Penyelenggara</th>
                                <th class="py-3 px-2">Kualifikasi (IPK/Maks Ortu)</th>
                                <th class="py-3 px-2">Batas Waktu</th>
                                <th class="py-3 px-2 text-center">Akses Klik</th>
                                <th class="py-3 px-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scholarships as $sch)
                                <tr class="border-b border-zinc-800/30 hover:bg-zinc-800/10 transition-colors">
                                    <td class="py-3 px-2">
                                        <div class="font-semibold text-zinc-100 flex items-center space-x-1.5">
                                            @if($sch->is_featured)
                                                <span class="w-2 h-2 rounded-full bg-amber-400" title="Unggulan / Sponsor"></span>
                                            @endif
                                            <span>{{ $sch->nama_beasiswa }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-500 mt-0.5">{{ $sch->penyelenggara }}</div>
                                    </td>
                                    <td class="py-3 px-2 font-medium">
                                        <div>IPK &ge; {{ number_format($sch->ipk_minimum, 2) }}</div>
                                        <div class="text-zinc-500 text-[10px] mt-0.5">Rp {{ number_format($sch->batas_penghasilan, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="py-3 px-2">
                                        <div class="{{ $sch->batas_waktu->isPast() ? 'text-red-400 font-semibold' : 'text-zinc-400' }}">
                                            {{ $sch->batas_waktu->format('d-m-Y') }}
                                        </div>
                                        <div class="text-[10px] text-zinc-500 mt-0.5">{{ $sch->batas_waktu->format('H:i') }}</div>
                                    </td>
                                    <td class="py-3 px-2 text-center font-bold font-title text-zinc-200">
                                        🔥 {{ $sch->jumlah_klik }}
                                    </td>
                                    <td class="py-3 px-2 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Edit Button -->
                                            <button @click="openEdit({
                                                id: {{ $sch->id }},
                                                nama_beasiswa: '{{ addslashes($sch->nama_beasiswa) }}',
                                                url_tautan: '{{ $sch->url_tautan }}',
                                                penyelenggara: '{{ addslashes($sch->penyelenggara) }}',
                                                deskripsi: '{{ addslashes($sch->deskripsi) }}',
                                                semester_min: {{ $sch->semester_min }},
                                                semester_max: {{ $sch->semester_max }},
                                                ipk_minimum: {{ $sch->ipk_minimum }},
                                                batas_penghasilan: {{ $sch->batas_penghasilan }},
                                                skor_status_c2: {{ $sch->skor_status_c2 }},
                                                is_featured: {{ $sch->is_featured ? 1 : 0 }},
                                                status_aktif: {{ $sch->status_aktif ? 1 : 0 }},
                                                formatted_date: '{{ $sch->batas_waktu->format('Y-m-d\TH:i') }}'
                                            })" class="p-1.5 rounded-lg border border-zinc-800 text-zinc-400 hover:text-emerald-400 hover:border-emerald-500/20 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>

                                            <!-- Delete Form -->
                                            <form action="{{ route('admin.scholarships.destroy', $sch->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus beasiswa ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg border border-zinc-800 text-zinc-400 hover:text-red-400 hover:border-red-500/20 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Premium Users Listing -->
            <div class="bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-6 shadow-xl mt-8">
                <h3 class="font-title font-bold text-lg text-white mb-4">Daftar Akun Premium (Total: {{ $premiumUsers->count() }})</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs text-zinc-300">
                        <thead>
                            <tr class="border-b border-zinc-800 text-zinc-500 font-bold">
                                <th class="py-3 px-2">Nama Lengkap</th>
                                <th class="py-3 px-2">Email</th>
                                <th class="py-3 px-2">Status Akademik</th>
                                <th class="py-3 px-2">Terakhir Aktif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($premiumUsers as $pu)
                                <tr class="border-b border-zinc-800/30 hover:bg-zinc-800/10 transition-colors">
                                    <td class="py-3 px-2 font-semibold text-zinc-100">{{ $pu->nama_lengkap }}</td>
                                    <td class="py-3 px-2 text-zinc-400">{{ $pu->email }}</td>
                                    <td class="py-3 px-2 text-zinc-400">{{ $pu->status_akademik }}</td>
                                    <td class="py-3 px-2 text-zinc-400">
                                        {{ $pu->last_active_at ? $pu->last_active_at->format('d-m-Y H:i') : 'Belum pernah aktif' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-zinc-500">Tidak ada akun premium terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right: Weighting Editor Panel -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-6 shadow-xl">
                <h3 class="font-title font-bold text-lg text-white mb-2">Bobot Kriteria SPK</h3>
                <p class="text-zinc-500 text-[11px] mb-6">Konfigurasi bobot penilaian terbobot SAW. Jumlah total seluruh bobot wajib sama dengan <strong>1.0 (100%)</strong>.</p>

                <form action="{{ route('admin.weights.update') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    @foreach($currentWeights as $cw)
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-semibold text-zinc-300">
                                <span>{{ $cw->kode_kriteria }} - {{ $cw->nama_kriteria }}</span>
                                <span class="text-zinc-500" x-text="(weights['{{ $cw->id }}'] * 100).toFixed(0) + '%'"></span>
                            </div>
                            <div class="relative flex items-center">
                                <input type="number" name="weights[{{ $cw->id }}]" step="0.01" min="0.00" max="1.00" 
                                    x-model.number="weights['{{ $cw->id }}']"
                                    class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-2.5 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-xs transition-all duration-300">
                            </div>
                        </div>
                    @endforeach

                    <!-- Weight Validation Info -->
                    <div class="p-3.5 rounded-xl text-[11px] font-semibold border flex items-center justify-between"
                        :class="totalWeight === 1.0 ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border-red-500/20 text-red-400'">
                        <span>Total Akumulasi Bobot:</span>
                        <span class="font-title text-sm font-black" x-text="(totalWeight * 100).toFixed(1) + '%'"></span>
                    </div>

                    <button type="submit" :disabled="totalWeight !== 1.0"
                        class="w-full py-3 bg-gradient-to-r from-emerald-500 to-blue-600 hover:from-emerald-400 hover:to-blue-500 text-zinc-950 font-bold rounded-xl text-xs transition-all shadow-md shadow-emerald-500/10 disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan & Publikasikan Bobot
                    </button>
                </form>
            </div>

            <!-- System Settings Panel -->
            <div class="bg-zinc-900/40 backdrop-blur-md border border-zinc-800/80 rounded-3xl p-6 shadow-xl mt-6">
                <h3 class="font-title font-bold text-lg text-white mb-2">Setelan Sistem</h3>
                <p class="text-zinc-500 text-[11px] mb-6">Atur biaya langganan akun premium dan limit masa tidak aktif akun sebelum dihapus otomatis.</p>

                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Premium Subscription Fee -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-300">Biaya Langganan Premium</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-xs font-bold text-zinc-500">Rp</span>
                            <input type="number" name="premium_price" value="{{ $settings['premium_price'] ?? 150000 }}" min="0" required
                                class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl pl-10 pr-4 py-2.5 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-xs transition-all duration-300">
                        </div>
                    </div>

                    <!-- Inactivity Limit Days -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-300">Limit Tidak Aktif (Hari)</label>
                        <div class="relative flex items-center">
                            <input type="number" name="inactivity_limit_days" value="{{ $settings['inactivity_limit_days'] ?? 90 }}" min="1" required
                                class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-emerald-500 rounded-xl px-4 py-2.5 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 text-xs transition-all duration-300">
                        </div>
                        <span class="text-[9px] text-zinc-500 block leading-normal mt-1">Akun yang tidak login/aktif selama waktu ini akan dihapus otomatis saat pembersihan dijalankan.</span>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold rounded-xl text-xs transition-all shadow-md">
                        Simpan Setelan
                    </button>
                </form>

                <!-- Inactivity Account Purging -->
                <div class="border-t border-zinc-800/80 pt-5 mt-5">
                    <h4 class="text-xs font-bold text-zinc-300 mb-2">Pembersihan Akun Kedaluwarsa</h4>
                    <form action="{{ route('admin.users.purge') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen semua akun yang tidak aktif melebihi batas hari?')">
                        @csrf
                        <button type="submit"
                            class="w-full py-2.5 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 font-bold rounded-xl text-xs transition-all flex items-center justify-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            <span>Hapus Akun Kedaraluwarsa</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- ==================== ADD MODAL ==================== -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAddModal = false"></div>
            
            <div class="relative bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 w-full max-w-2xl shadow-2xl z-10 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center border-b border-zinc-800 pb-3">
                    <h3 class="font-title font-bold text-lg text-white">Tambah Beasiswa Baru</h3>
                    <button @click="showAddModal = false" class="text-zinc-500 hover:text-zinc-300">&times;</button>
                </div>

                <form action="{{ route('admin.scholarships.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1 col-span-2">
                            <label class="font-semibold text-zinc-300">Nama Beasiswa</label>
                            <input type="text" name="nama_beasiswa" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Penyelenggara</label>
                            <input type="text" name="penyelenggara" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">URL Tautan Asli</label>
                            <input type="url" name="url_tautan" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1 col-span-2">
                            <label class="font-semibold text-zinc-300">Deskripsi Ringkas</label>
                            <textarea name="deskripsi" rows="3" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Semester Min</label>
                            <input type="number" name="semester_min" value="1" min="1" max="14" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Semester Max</label>
                            <input type="number" name="semester_max" value="8" min="1" max="14" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Syarat IPK Minimum</label>
                            <input type="number" step="0.01" name="ipk_minimum" value="3.00" min="0.0" max="4.0" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Batas Maks Penghasilan Ortu</label>
                            <input type="number" name="batas_penghasilan" value="4000000" min="0" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Skor Status Kriteria C2 (1 - 3)</label>
                            <select name="skor_status_c2" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100 appearance-none">
                                <option value="3">3 (Menerima Semua Status)</option>
                                <option value="2">2 (Aktif & Transfer)</option>
                                <option value="1">1 (Hanya Aktif Regular)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Batas Waktu Pendaftaran</label>
                            <input type="datetime-local" name="batas_waktu" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6 py-2 border-t border-zinc-800 mt-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="rounded border-zinc-800 bg-zinc-950 text-emerald-500 focus:ring-0">
                            <span class="font-semibold text-zinc-300">Featured Listing (B2B Sponsor)</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="status_aktif" value="1" checked class="rounded border-zinc-800 bg-zinc-950 text-emerald-500 focus:ring-0">
                            <span class="font-semibold text-zinc-300">Status Aktif Dibuka</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-zinc-800 hover:bg-zinc-800 rounded-xl text-zinc-300 font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-emerald-500 hover:bg-emerald-400 text-zinc-950 font-bold rounded-xl shadow-lg shadow-emerald-500/10">Simpan Beasiswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== EDIT MODAL ==================== -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showEditModal = false"></div>
            
            <div class="relative bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 w-full max-w-2xl shadow-2xl z-10 space-y-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center border-b border-zinc-800 pb-3">
                    <h3 class="font-title font-bold text-lg text-white">Edit Beasiswa</h3>
                    <button @click="showEditModal = false" class="text-zinc-500 hover:text-zinc-300">&times;</button>
                </div>

                <form :action="'/admin/scholarships/' + editData.id" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1 col-span-2">
                            <label class="font-semibold text-zinc-300">Nama Beasiswa</label>
                            <input type="text" name="nama_beasiswa" x-model="editData.nama_beasiswa" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Penyelenggara</label>
                            <input type="text" name="penyelenggara" x-model="editData.penyelenggara" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">URL Tautan Asli</label>
                            <input type="url" name="url_tautan" x-model="editData.url_tautan" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1 col-span-2">
                            <label class="font-semibold text-zinc-300">Deskripsi Ringkas</label>
                            <textarea name="deskripsi" x-model="editData.deskripsi" rows="3" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Semester Min</label>
                            <input type="number" name="semester_min" x-model.number="editData.semester_min" min="1" max="14" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Semester Max</label>
                            <input type="number" name="semester_max" x-model.number="editData.semester_max" min="1" max="14" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Syarat IPK Minimum</label>
                            <input type="number" step="0.01" name="ipk_minimum" x-model="editData.ipk_minimum" min="0.0" max="4.0" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Batas Maks Penghasilan Ortu</label>
                            <input type="number" name="batas_penghasilan" x-model.number="editData.batas_penghasilan" min="0" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Skor Status Kriteria C2 (1 - 3)</label>
                            <select name="skor_status_c2" x-model.number="editData.skor_status_c2" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100 appearance-none">
                                <option value="3">3 (Menerima Semua Status)</option>
                                <option value="2">2 (Aktif & Transfer)</option>
                                <option value="1">1 (Hanya Aktif Regular)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-300">Batas Waktu Pendaftaran</label>
                            <input type="datetime-local" name="batas_waktu" x-model="editData.batas_waktu" class="w-full bg-zinc-950 border border-zinc-800 focus:border-emerald-500 rounded-lg p-2.5 text-zinc-100" required>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6 py-2 border-t border-zinc-800 mt-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" x-model="editData.is_featured" class="rounded border-zinc-800 bg-zinc-950 text-emerald-500 focus:ring-0">
                            <span class="font-semibold text-zinc-300">Featured Listing (B2B Sponsor)</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="status_aktif" value="1" x-model="editData.status_aktif" class="rounded border-zinc-800 bg-zinc-950 text-emerald-500 focus:ring-0">
                            <span class="font-semibold text-zinc-300">Status Aktif Dibuka</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-zinc-800 hover:bg-zinc-800 rounded-xl text-zinc-300 font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-emerald-500 hover:bg-emerald-400 text-zinc-950 font-bold rounded-xl shadow-lg shadow-emerald-500/10">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
