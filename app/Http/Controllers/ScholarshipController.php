<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Scholarship;
use App\Models\Recommendation;
use App\Models\Setting;
use App\Services\SpkEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ScholarshipController extends Controller
{
    // Instance SPK Engine Service
    protected SpkEngineService $spkEngine;

    /**
     * Konstruktor untuk inisialisasi SpkEngineService.
     */
    public function __construct(SpkEngineService $spkEngine)
    {
        $this->spkEngine = $spkEngine;
    }

    /**
     * Tampilkan formulir profil matchmaking untuk user.
     */
    public function index()
    {
        $user = auth()->user();

        // Jika user adalah admin, alihkan ke Admin Dashboard karena form matchmaking tidak berguna bagi admin
        if ($user && $user->is_admin) {
            return redirect()->route('admin.scholarships.index');
        }
        
        // Buat defaults data form diambil langsung dari profil user yang sedang login
        $defaults = [
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'semester' => $user->semester,
            'ipk' => $user->ipk,
            'status_akademik' => $user->status_akademik,
            'penghasilan_ortu' => $user->penghasilan_ortu,
        ];

        return view('dashboard', compact('defaults'));
    }

    /**
     * Proses kalkulasi kecocokan beasiswa (AJAX), log hasil, dan kembalikan JSON.
     */
    public function calculate(Request $request)
    {
        // Validasi parameter profil masukan dari form
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'email' => 'required|email|max:100',
            'semester' => 'required|integer|min:1|max:14',
            'ipk' => 'required|numeric|min:0.0|max:4.0',
            'status_akademik' => 'required|string|in:Aktif Regular,Aktif Transfer,Cuti',
            'penghasilan_ortu' => 'required|integer|min:0',
        ]);

        // 1. Simpan atau perbarui profil data akademik mahasiswa di database
        $user = User::updateOrCreate(
            ['email' => $validated['email']],
            [
                'nama_lengkap' => $validated['nama_lengkap'],
                'semester' => $validated['semester'],
                'ipk' => $validated['ipk'],
                'status_akademik' => $validated['status_akademik'],
                'penghasilan_ortu' => $validated['penghasilan_ortu'],
                'password' => Hash::make('password123'), // password cadangan standard
            ]
        );

        // 2. Bersihkan riwayat rekomendasi usang milik user ini
        Recommendation::where('user_id', $user->id)->delete();

        // 3. Jalankan pemrosesan SPK (Filtering Stage & SAW Ranking Stage)
        $recommendations = $this->spkEngine->calculateRecommendations($user);

        // 4. Catat peringkat hasil kalkulasi ke database log menggunakan Eloquent
        foreach ($recommendations as $rec) {
            Recommendation::create([
                'user_id' => $user->id,
                'scholarship_id' => $rec['scholarship']['id'],
                'nilai_preferensi' => $rec['nilai_preferensi'],
                'peringkat' => $rec['peringkat'],
                'parameter_input' => [
                    'semester' => $user->semester,
                    'ipk' => $user->ipk,
                    'status_akademik' => $user->status_akademik,
                    'penghasilan_ortu' => $user->penghasilan_ortu,
                ]
            ]);
        }

        // 5. Cari beasiswa aktif yang gugur (tidak lolos syarat IPK/penghasilan/deadline)
        $allScholarships = Scholarship::where('status_aktif', true)->get();
        $ineligible = $allScholarships->filter(function ($scholarship) use ($user) {
            $ipkFail = $user->ipk < $scholarship->ipk_minimum;
            $incomeFail = $user->penghasilan_ortu > $scholarship->batas_penghasilan;
            $expiredFail = $scholarship->batas_waktu && $scholarship->batas_waktu->isPast();
            return $ipkFail || $incomeFail || $expiredFail;
        })->map(function ($scholarship) use ($user) {
            $reasons = [];
            if ($user->ipk < $scholarship->ipk_minimum) {
                $reasons[] = "IPK kurang dari syarat minimum (" . number_format($scholarship->ipk_minimum, 2) . ")";
            }
            if ($user->penghasilan_ortu > $scholarship->batas_penghasilan) {
                $reasons[] = "Penghasilan orang tua melebihi batas maksimum (Rp " . number_format($scholarship->batas_penghasilan, 0, ',', '.') . ")";
            }
            if ($scholarship->batas_waktu && $scholarship->batas_waktu->isPast()) {
                $reasons[] = "Batas waktu pendaftaran telah terlampaui (" . $scholarship->batas_waktu->format('d-m-Y H:i') . ")";
            }
            $scholarship->gugur_reasons = implode(', ', $reasons);
            return $scholarship;
        })->values();

        // 6. Siapkan simulasi beasiswa premium sponsor terkunci
        $premiumScholarships = [
            [
                'nama_beasiswa' => 'Beasiswa Prestasi XL Axiata Future Leaders',
                'penyelenggara' => 'XL Axiata Tbk',
                'deskripsi' => 'Program pengembangan kepemimpinan intensif selama 2 tahun untuk mahasiswa berprestasi nasional.',
                'nilai_preferensi' => 0.985000,
                'skor_persen' => 98.5,
            ],
            [
                'nama_beasiswa' => 'Djarum Beasiswa Plus',
                'penyelenggara' => 'Djarum Foundation',
                'deskripsi' => 'Beasiswa prestasi untuk mahasiswa diploma dan sarjana dengan dana beasiswa serta pelatihan soft skills kepemimpinan.',
                'nilai_preferensi' => 0.942000,
                'skor_persen' => 94.2,
            ]
        ];

        return response()->json([
            'success' => true,
            'user' => [
                'nama_lengkap' => $user->nama_lengkap,
                'email' => $user->email,
                'semester' => $user->semester,
                'ipk' => $user->ipk,
                'status_akademik' => $user->status_akademik,
                'penghasilan_ortu' => $user->penghasilan_ortu,
                'is_premium' => (bool) $user->is_premium,
            ],
            'recommendations' => $recommendations,
            'ineligible' => $ineligible,
            'premium_scholarships' => $premiumScholarships
        ]);
    }

    /**
     * Catat statistik klik unik mahasiswa pada tautan detail beasiswa.
     */
    public function trackClick(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
        $scholarship = Scholarship::findOrFail($id);

        if ($user) {
            // Cek apakah user bersangkutan sudah pernah mengeklik beasiswa ini sebelumnya
            $exists = \App\Models\ClickLog::where('user_id', $user->id)
                ->where('scholarship_id', $scholarship->id)
                ->exists();

            if (!$exists) {
                \App\Models\ClickLog::create([
                    'user_id' => $user->id,
                    'scholarship_id' => $scholarship->id,
                    'clicked_at' => now(),
                ]);

                // Naikkan akumulasi jumlah klik beasiswa
                $scholarship->increment('jumlah_klik');
            }
        }

        return response()->json([
            'success' => true,
            'url' => $scholarship->url_tautan,
            'jumlah_klik' => $scholarship->jumlah_klik
        ]);
    }

    /**
     * Tampilkan katalog beasiswa aktif dengan fitur pencarian dan kualifikasi IPK.
     */
    public function catalog(Request $request)
    {
        $query = Scholarship::where('status_aktif', true);

        // Filter kata kunci nama beasiswa / penyelenggara
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_beasiswa', 'like', $searchTerm)
                  ->orWhere('penyelenggara', 'like', $searchTerm)
                  ->orWhere('deskripsi', 'like', $searchTerm);
            });
        }

        // Filter kesesuaian syarat IPK maksimum
        if ($request->has('min_ipk') && is_numeric($request->min_ipk)) {
            $query->where('ipk_minimum', '<=', $request->min_ipk);
        }

        $scholarships = $query->orderBy('id', 'desc')->get();

        return view('catalog', compact('scholarships'));
    }

    /**
     * Tampilkan halaman penawaran paket keanggotaan Premium.
     */
    public function premium()
    {
        // Ambil harga premium dari pengaturan database (default Rp 150.000)
        $premiumPrice = Setting::where('key', 'premium_price')->value('value') ?? 150000;

        return view('premium', compact('premiumPrice'));
    }

    /**
     * Ugrade akun user saat ini ke Premium (Opsional, dinonaktifkan di tampilan demi kepentingan sampel).
     */
    public function upgradePremium(Request $request)
    {
        $user = auth()->user();
        $user->is_premium = true;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Akun Anda berhasil ditingkatkan ke Premium! Semua rekomendasi kini terbuka.');
    }
}
