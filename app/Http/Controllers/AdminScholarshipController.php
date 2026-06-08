<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\Criteria;
use App\Models\Weighting;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminScholarshipController extends Controller
{
    /**
     * Tampilkan panel admin utama (Mengambil beasiswa, kriteria, bobot, user premium, dan setelan).
     */
    public function index()
    {
        // Ambil semua beasiswa, urutkan dari yang terbaru
        $scholarships = Scholarship::orderBy('id', 'desc')->get();
        
        // Ambil semua data kriteria
        $criteria = Criteria::all();
        
        // Ambil data bobot kriteria terhubung dengan kode kriteria
        $currentWeights = Weighting::join('criteria', 'weighting.criteria_id', '=', 'criteria.id')
            ->select('weighting.*', 'criteria.kode_kriteria', 'criteria.nama_kriteria')
            ->get();

        // Ambil daftar user berstatus premium (bukan admin)
        $premiumUsers = User::where('is_premium', true)
            ->where('is_admin', false)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        // Ambil semua setelan konfigurasi dalam bentuk key-value
        $settings = Setting::pluck('value', 'key');

        return view('admin.scholarships', compact(
            'scholarships', 
            'criteria', 
            'currentWeights', 
            'premiumUsers', 
            'settings'
        ));
    }

    /**
     * Tambahkan data beasiswa baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi input data beasiswa baru
        $validated = $request->validate([
            'nama_beasiswa' => 'required|string|max:200',
            'url_tautan' => 'required|url',
            'penyelenggara' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'semester_min' => 'required|integer|min:1|max:14',
            'semester_max' => 'required|integer|min:1|max:14|gte:semester_min',
            'ipk_minimum' => 'required|numeric|min:0.0|max:4.0',
            'batas_penghasilan' => 'required|integer|min:0',
            'skor_status_c2' => 'required|integer|min:1|max:3',
            'batas_waktu' => 'required|date_format:Y-m-d\TH:i',
            'is_featured' => 'sometimes|boolean',
            'status_aktif' => 'sometimes|boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['status_aktif'] = $request->has('status_aktif');

        // Buat record beasiswa baru
        Scholarship::create($validated);

        return redirect()->back()->with('success', 'Beasiswa berhasil ditambahkan!');
    }

    /**
     * Perbarui data beasiswa yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        // Cari data beasiswa berdasarkan ID
        $scholarship = Scholarship::findOrFail($id);

        // Validasi input pembaruan data beasiswa
        $validated = $request->validate([
            'nama_beasiswa' => 'required|string|max:200',
            'url_tautan' => 'required|url',
            'penyelenggara' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'semester_min' => 'required|integer|min:1|max:14',
            'semester_max' => 'required|integer|min:1|max:14|gte:semester_min',
            'ipk_minimum' => 'required|numeric|min:0.0|max:4.0',
            'batas_penghasilan' => 'required|integer|min:0',
            'skor_status_c2' => 'required|integer|min:1|max:3',
            'batas_waktu' => 'required|date_format:Y-m-d\TH:i',
            'is_featured' => 'sometimes|boolean',
            'status_aktif' => 'sometimes|boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['status_aktif'] = $request->has('status_aktif');

        // Lakukan update data
        $scholarship->update($validated);

        return redirect()->back()->with('success', 'Beasiswa berhasil diperbarui!');
    }

    /**
     * Hapus data beasiswa dari database.
     */
    public function destroy($id)
    {
        // Cari beasiswa dan hapus
        $scholarship = Scholarship::findOrFail($id);
        $scholarship->delete();

        return redirect()->back()->with('success', 'Beasiswa berhasil dihapus!');
    }

    /**
     * Perbarui bobot kriteria SPK dengan validasi total jumlah bobot harus 1.0 (100%).
     */
    public function updateWeights(Request $request)
    {
        $request->validate([
            'weights' => 'required|array',
            'weights.*' => 'required|numeric|min:0.0|max:1.0',
        ]);

        // Pastikan jumlah total bobot bernilai 1.0 (100%)
        $totalSum = array_sum($request->weights);
        if (abs($totalSum - 1.0) > 0.0001) {
            return redirect()->back()->with('error', 'Total bobot kriteria harus sama dengan 1.0 (100%). Total saat ini: ' . ($totalSum * 100) . '%');
        }

        // Perbarui setiap bobot kriteria dan naikkan versinya untuk audit trail
        foreach ($request->weights as $weightId => $value) {
            $w = Weighting::findOrFail($weightId);
            $w->update([
                'bobot' => $value,
                'versi_bobot' => $w->versi_bobot + 1,
                'berlaku_dari' => now()->toDateString(),
                'ditetapkan_oleh' => 'Administrator',
            ]);
        }

        return redirect()->back()->with('success', 'Bobot kriteria SPK berhasil diperbarui!');
    }

    /**
     * Simpan pengaturan harga premium dan batas hari tidak aktif akun.
     */
    public function updateSettings(Request $request)
    {
        // Validasi input setelan sistem
        $request->validate([
            'premium_price' => 'required|integer|min:0',
            'inactivity_limit_days' => 'required|integer|min:1',
        ]);

        // Simpan atau perbarui data setelan di database
        Setting::updateOrCreate(['key' => 'premium_price'], ['value' => $request->premium_price]);
        Setting::updateOrCreate(['key' => 'inactivity_limit_days'], ['value' => $request->inactivity_limit_days]);

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil disimpan!');
    }

    /**
     * Hapus otomatis akun mahasiswa tidak aktif yang melebihi batas waktu (menghindari user sampah).
     */
    public function purgeInactiveUsers()
    {
        // Ambil batas hari tidak aktif dari database (default 90 hari)
        $limitDays = Setting::where('key', 'inactivity_limit_days')->value('value') ?? 90;
        $cutoffDate = now()->subDays((int)$limitDays);

        // Cari akun non-admin yang last_active_at-nya melewati batas waktu (atau belum pernah aktif sama sekali)
        $inactiveQuery = User::where('is_admin', false)
            ->where(function($query) use ($cutoffDate) {
                $query->where('last_active_at', '<', $cutoffDate)
                      ->orWhereNull('last_active_at');
            });

        // Hitung jumlah yang dihapus dan lakukan proses penghapusan
        $deletedCount = $inactiveQuery->count();
        $inactiveQuery->delete();

        return redirect()->back()->with('success', "Proses pembersihan selesai! Sebanyak {$deletedCount} akun tidak aktif berhasil terhapus.");
    }
}
