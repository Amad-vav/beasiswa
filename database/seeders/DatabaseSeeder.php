<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Criteria;
use App\Models\Weighting;
use App\Models\Scholarship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Default Settings
        \App\Models\Setting::create([
            'key' => 'premium_price',
            'value' => '150000'
        ]);

        \App\Models\Setting::create([
            'key' => 'inactivity_limit_days',
            'value' => '90'
        ]);

        // 1. Seed Accounts: Andi (Regular), Budi (Premium), and Admin
        // Regular User: Andi Pratama
        User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
            'is_premium' => false,
            'is_admin' => false,
            'last_active_at' => now(),
        ]);

        // Premium User: Budi Premium
        User::create([
            'nama_lengkap' => 'Budi Premium',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.60,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 4000000,
            'is_premium' => true,
            'is_admin' => false,
            'last_active_at' => now(),
        ]);

        // Admin User: Admin ScholarMatch
        User::create([
            'nama_lengkap' => 'Admin ScholarMatch',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'semester' => 7,
            'ipk' => 3.80,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 5000000,
            'is_premium' => true,
            'is_admin' => true,
            'last_active_at' => now(),
        ]);

        // 2. Seed Criteria (C1 - C4)
        $c1 = Criteria::create([
            'kode_kriteria' => 'C1',
            'nama_kriteria' => 'Kesesuaian Semester',
            'dimensi' => 'Akademik',
            'tipe_kriteria' => 'Benefit',
            'deskripsi' => 'Beasiswa dengan rentang semester lebih lebar memberi kesesuaian lebih tinggi bagi mahasiswa.',
        ]);

        $c2 = Criteria::create([
            'kode_kriteria' => 'C2',
            'nama_kriteria' => 'Status Akademik',
            'dimensi' => 'Akademik',
            'tipe_kriteria' => 'Benefit',
            'deskripsi' => 'Beasiswa yang menerima semua status kemahasiswaan memiliki relevansi lebih luas.',
        ]);

        $c3 = Criteria::create([
            'kode_kriteria' => 'C3',
            'nama_kriteria' => 'Kesesuaian IPK',
            'dimensi' => 'Sosio-Ekonomi',
            'tipe_kriteria' => 'Benefit',
            'deskripsi' => 'Skor kelayakan IPK mahasiswa terhadap syarat beasiswa. Dihitung dari surplus IPK di atas syarat minimum, dikonversi ke Skala 1-5.',
        ]);

        $c4 = Criteria::create([
            'kode_kriteria' => 'C4',
            'nama_kriteria' => 'Kesesuaian Penghasilan',
            'dimensi' => 'Sosio-Ekonomi',
            'tipe_kriteria' => 'Benefit',
            'deskripsi' => 'Skor kelayakan penghasilan orang tua terhadap batas maksimum beasiswa. Semakin besar surplus = semakin layak. Dikonversi ke Skala 1-5.',
        ]);

        // 3. Seed Weighting (W)
        Weighting::create([
            'criteria_id' => $c1->id,
            'bobot' => 0.25,
            'versi_bobot' => 1,
            'berlaku_dari' => '2026-06-08',
            'ditetapkan_oleh' => 'Admin Panel',
        ]);

        Weighting::create([
            'criteria_id' => $c2->id,
            'bobot' => 0.20,
            'versi_bobot' => 1,
            'berlaku_dari' => '2026-06-08',
            'ditetapkan_oleh' => 'Admin Panel',
        ]);

        Weighting::create([
            'criteria_id' => $c3->id,
            'bobot' => 0.30,
            'versi_bobot' => 1,
            'berlaku_dari' => '2026-06-08',
            'ditetapkan_oleh' => 'Admin Panel',
        ]);

        Weighting::create([
            'criteria_id' => $c4->id,
            'bobot' => 0.25,
            'versi_bobot' => 1,
            'berlaku_dari' => '2026-06-08',
            'ditetapkan_oleh' => 'Admin Panel',
        ]);

        // 4. Seed Alternatif Beasiswa (Total 13 beasiswa untuk pengujian bervariasi)
        // A1 (KIP-Kuliah) - Lolos (Sangat Direkomendasikan)
        Scholarship::create([
            'nama_beasiswa' => 'A1: Beasiswa KIP-Kuliah',
            'url_tautan' => 'https://kip-kuliah.kemdikbud.go.id',
            'penyelenggara' => 'Kementerian Pendidikan dan Kebudayaan',
            'deskripsi' => 'Bantuan biaya pendidikan dari pemerintah bagi lulusan SMA sederajat yang memiliki potensi akademik baik tetapi keterbatasan ekonomi.',
            'semester_min' => 1,
            'semester_max' => 8,
            'ipk_minimum' => 2.50,
            'batas_penghasilan' => 4000000,
            'skor_status_c2' => 3, // semua status
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(30),
            'jumlah_klik' => 125,
        ]);

        // A2 (Beasiswa Unggulan Kemendikbud) - Lolos (Direkomendasikan)
        Scholarship::create([
            'nama_beasiswa' => 'A2: Beasiswa Unggulan Kemendikbud',
            'url_tautan' => 'https://beasiswaunggulan.kemdikbud.go.id',
            'penyelenggara' => 'Kementerian Pendidikan dan Kebudayaan',
            'deskripsi' => 'Beasiswa dalam negeri untuk jenjang Sarjana, Magister dan Doktor bagi mahasiswa berprestasi tingkat nasional.',
            'semester_min' => 1,
            'semester_max' => 6,
            'ipk_minimum' => 3.25,
            'batas_penghasilan' => 3500000,
            'skor_status_c2' => 2, // aktif+transfer
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(15),
            'jumlah_klik' => 84,
        ]);

        // A3 (Beasiswa Yayasan XYZ) - Gugur (Penghasilan Ortu melebihi batas)
        Scholarship::create([
            'nama_beasiswa' => 'A3: Beasiswa Yayasan XYZ',
            'url_tautan' => 'https://yayasanxyz.org',
            'penyelenggara' => 'Yayasan Peduli Pendidikan XYZ',
            'deskripsi' => 'Beasiswa swasta yang ditujukan untuk membantu mahasiswa semester pertengahan dengan prestasi akademik tinggi.',
            'semester_min' => 3,
            'semester_max' => 6,
            'ipk_minimum' => 3.00,
            'batas_penghasilan' => 2500000,
            'skor_status_c2' => 1, // aktif saja
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(20),
            'jumlah_klik' => 12,
        ]);

        // A4 (Beasiswa Bank Mandiri) - Lolos (Featured & Premium Gated)
        Scholarship::create([
            'nama_beasiswa' => 'A4: Beasiswa Bank Mandiri Prestasi',
            'url_tautan' => 'https://bankmandiri.co.id/beasiswa',
            'penyelenggara' => 'PT Bank Mandiri (Persero) Tbk',
            'deskripsi' => 'Beasiswa prestasi akademik untuk mahasiswa semester 5 ke atas sebagai bentuk CSR kepedulian pendidikan.',
            'semester_min' => 5,
            'semester_max' => 8,
            'ipk_minimum' => 3.30,
            'batas_penghasilan' => 5000000,
            'skor_status_c2' => 2,
            'is_featured' => true,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(45),
            'jumlah_klik' => 210,
        ]);

        // A5 (Djarum Beasiswa Plus) - Lolos (Featured)
        Scholarship::create([
            'nama_beasiswa' => 'A5: Djarum Beasiswa Plus',
            'url_tautan' => 'https://djarumbeasiswaplus.org',
            'penyelenggara' => 'Djarum Foundation',
            'deskripsi' => 'Program beasiswa prestasi dengan pelatihan soft skills kepemimpinan dan karakter bagi mahasiswa D4/S1.',
            'semester_min' => 4,
            'semester_max' => 6,
            'ipk_minimum' => 3.20,
            'batas_penghasilan' => 4500000,
            'skor_status_c2' => 1,
            'is_featured' => true,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(10),
            'jumlah_klik' => 345,
        ]);

        // A6 (Beasiswa PPA) - Lolos
        Scholarship::create([
            'nama_beasiswa' => 'A6: Beasiswa Peningkatan Prestasi Akademik (PPA)',
            'url_tautan' => 'https://dikti.kemdikbud.go.id',
            'penyelenggara' => 'Ditjen Dikti Ristek Kemendikbud',
            'deskripsi' => 'Bantuan biaya pendidikan dari pemerintah khusus untuk mahasiswa aktif yang memiliki prestasi akademik memuaskan.',
            'semester_min' => 2,
            'semester_max' => 8,
            'ipk_minimum' => 3.00,
            'batas_penghasilan' => 3000000,
            'skor_status_c2' => 3,
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(5),
            'jumlah_klik' => 189,
        ]);

        // A7 (Beasiswa BCA Finance) - Lolos
        Scholarship::create([
            'nama_beasiswa' => 'A7: Beasiswa BCA Finance Peduli',
            'url_tautan' => 'https://bcafinance.co.id/beasiswa',
            'penyelenggara' => 'PT BCA Finance',
            'deskripsi' => 'Dukungan finansial bagi mahasiswa S1 berprestasi di seluruh Indonesia dengan indeks prestasi tinggi.',
            'semester_min' => 4,
            'semester_max' => 8,
            'ipk_minimum' => 3.40,
            'batas_penghasilan' => 6000000,
            'skor_status_c2' => 2,
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(60),
            'jumlah_klik' => 95,
        ]);

        // A8 (Beasiswa Supersemar) - Gugur (Batas Waktu Kedaluwarsa)
        Scholarship::create([
            'nama_beasiswa' => 'A8: Beasiswa Yayasan Supersemar',
            'url_tautan' => 'https://supersemar.or.id',
            'penyelenggara' => 'Yayasan Supersemar',
            'deskripsi' => 'Program bantuan biaya hidup bagi mahasiswa aktif berprestasi di perguruan tinggi negeri.',
            'semester_min' => 3,
            'semester_max' => 8,
            'ipk_minimum' => 2.50,
            'batas_penghasilan' => 3000000,
            'skor_status_c2' => 3,
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->subDays(5), // Kedaluwarsa!
            'jumlah_klik' => 5,
        ]);

        // A9 (Beasiswa Tanoto Foundation) - Gugur (IPK Andi 3.45 < Syarat 3.50)
        Scholarship::create([
            'nama_beasiswa' => 'A9: Beasiswa Tanoto Foundation TELADAN',
            'url_tautan' => 'https://tanotofoundation.org',
            'penyelenggara' => 'Tanoto Foundation',
            'deskripsi' => 'Program pengembangan kepemimpinan dan beasiswa kepemimpinan untuk mahasiswa semester pertama.',
            'semester_min' => 1,
            'semester_max' => 8,
            'ipk_minimum' => 3.50,
            'batas_penghasilan' => 5000000,
            'skor_status_c2' => 2,
            'is_featured' => true,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(90),
            'jumlah_klik' => 78,
        ]);

        // A10 (Beasiswa Van Deventer-Maas) - Gugur (Penghasilan Ortu melebihi batas)
        Scholarship::create([
            'nama_beasiswa' => 'A10: Beasiswa Van Deventer-Maas Indonesia (VDMI)',
            'url_tautan' => 'https://vandeventermaas.or.id',
            'penyelenggara' => 'Van Deventer-Maas Stichting',
            'deskripsi' => 'Beasiswa luar negeri yang memberikan dana pendidikan bulanan bagi mahasiswa Indonesia kurang mampu.',
            'semester_min' => 2,
            'semester_max' => 8,
            'ipk_minimum' => 3.00,
            'batas_penghasilan' => 2000000,
            'skor_status_c2' => 3,
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(25),
            'jumlah_klik' => 43,
        ]);

        // A11 (Beasiswa Sobat Bumi Pertamina) - Lolos (Featured)
        Scholarship::create([
            'nama_beasiswa' => 'A11: Beasiswa Pertamina Sobat Bumi',
            'url_tautan' => 'https://pertaminafoundation.org/beasiswa',
            'penyelenggara' => 'Pertamina Foundation',
            'deskripsi' => 'Apresiasi kepada mahasiswa berprestasi secara akademik, aktif organisasi, dan memiliki kepedulian lingkungan.',
            'semester_min' => 5,
            'semester_max' => 8,
            'ipk_minimum' => 3.00,
            'batas_penghasilan' => 4000000,
            'skor_status_c2' => 2,
            'is_featured' => true,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(12),
            'jumlah_klik' => 256,
        ]);

        // A12 (Beasiswa DataPrint) - Gugur (Batas Waktu Kedaluwarsa)
        Scholarship::create([
            'nama_beasiswa' => 'A12: Beasiswa DataPrint Indonesia',
            'url_tautan' => 'https://beasiswadataprint.com',
            'penyelenggara' => 'DataPrint Indonesia',
            'deskripsi' => 'Beasiswa tunai dari produk tinta isi ulang DataPrint untuk pelajar dan mahasiswa aktif.',
            'semester_min' => 1,
            'semester_max' => 8,
            'ipk_minimum' => 2.80,
            'batas_penghasilan' => 3500000,
            'skor_status_c2' => 3,
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->subDays(2), // Kedaluwarsa!
            'jumlah_klik' => 18,
        ]);

        // A13 (Beasiswa Karya Salemba Empat) - Lolos
        Scholarship::create([
            'nama_beasiswa' => 'A13: Beasiswa Karya Salemba Empat (KSE)',
            'url_tautan' => 'https://karyasalemba4.org',
            'penyelenggara' => 'Yayasan Karya Salemba Empat',
            'deskripsi' => 'Dukungan finansial dan pembinaan karir bagi mahasiswa aktif berprestasi di tingkat perguruan tinggi negeri.',
            'semester_min' => 2,
            'semester_max' => 8,
            'ipk_minimum' => 3.00,
            'batas_penghasilan' => 5000000,
            'skor_status_c2' => 3,
            'is_featured' => false,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(40),
            'jumlah_klik' => 142,
        ]);
    }
}
