<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Criteria;
use App\Models\Weighting;
use App\Services\SpkEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpkEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_spk_matchmaking_calculations_match_pdf_exactly()
    {
        // 1. Seed Criteria C1 - C4
        $c1 = Criteria::create([
            'kode_kriteria' => 'C1',
            'nama_kriteria' => 'Kesesuaian Semester',
            'dimensi' => 'Akademik',
            'tipe_kriteria' => 'Benefit',
        ]);

        $c2 = Criteria::create([
            'kode_kriteria' => 'C2',
            'nama_kriteria' => 'Status Akademik',
            'dimensi' => 'Akademik',
            'tipe_kriteria' => 'Benefit',
        ]);

        $c3 = Criteria::create([
            'kode_kriteria' => 'C3',
            'nama_kriteria' => 'Kesesuaian IPK',
            'dimensi' => 'Sosio-Ekonomi',
            'tipe_kriteria' => 'Benefit',
        ]);

        $c4 = Criteria::create([
            'kode_kriteria' => 'C4',
            'nama_kriteria' => 'Kesesuaian Penghasilan',
            'dimensi' => 'Sosio-Ekonomi',
            'tipe_kriteria' => 'Benefit',
        ]);

        // 2. Seed Weightings (0.25, 0.20, 0.30, 0.25)
        Weighting::create(['criteria_id' => $c1->id, 'bobot' => 0.25, 'versi_bobot' => 1, 'berlaku_dari' => '2026-06-08', 'ditetapkan_oleh' => 'Admin']);
        Weighting::create(['criteria_id' => $c2->id, 'bobot' => 0.20, 'versi_bobot' => 1, 'berlaku_dari' => '2026-06-08', 'ditetapkan_oleh' => 'Admin']);
        Weighting::create(['criteria_id' => $c3->id, 'bobot' => 0.30, 'versi_bobot' => 1, 'berlaku_dari' => '2026-06-08', 'ditetapkan_oleh' => 'Admin']);
        Weighting::create(['criteria_id' => $c4->id, 'bobot' => 0.25, 'versi_bobot' => 1, 'berlaku_dari' => '2026-06-08', 'ditetapkan_oleh' => 'Admin']);

        // 3. Seed Scholarships A1, A2, A3
        $a1 = Scholarship::create([
            'nama_beasiswa' => 'A1: Beasiswa KIP-Kuliah',
            'url_tautan' => 'https://kip-kuliah.kemdikbud.go.id',
            'penyelenggara' => 'Kemendikbud',
            'semester_min' => 1,
            'semester_max' => 8,
            'ipk_minimum' => 2.50,
            'batas_penghasilan' => 4000000,
            'skor_status_c2' => 3,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(30),
        ]);

        $a2 = Scholarship::create([
            'nama_beasiswa' => 'A2: Beasiswa Unggulan Kemendikbud',
            'url_tautan' => 'https://beasiswaunggulan.kemdikbud.go.id',
            'penyelenggara' => 'Kemendikbud',
            'semester_min' => 1,
            'semester_max' => 6,
            'ipk_minimum' => 3.25,
            'batas_penghasilan' => 3500000,
            'skor_status_c2' => 2,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(15),
        ]);

        $a3 = Scholarship::create([
            'nama_beasiswa' => 'A3: Beasiswa Yayasan XYZ',
            'url_tautan' => 'https://yayasanxyz.org',
            'penyelenggara' => 'Yayasan XYZ',
            'semester_min' => 3,
            'semester_max' => 6,
            'ipk_minimum' => 3.00,
            'batas_penghasilan' => 2500000,
            'skor_status_c2' => 1,
            'status_aktif' => true,
            'batas_waktu' => now()->addDays(20),
        ]);

        // 4. Create User Andi Pratama
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => 'password',
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
            'is_premium' => false,
        ]);

        // Run calculation
        $service = new SpkEngineService();
        $results = $service->calculateRecommendations($user);

        // Assertions
        // A3 should be filtered out (parent income 3m > 2.5m A3 limit)
        $a3Result = $results->firstWhere('scholarship.id', $a3->id);
        $this->assertNull($a3Result, 'A3 should be filtered out because income exceeds the maximum limit');

        // Total results should be 2 (A1 and A2)
        $this->assertCount(2, $results);

        // A1 (KIP-Kuliah) checks:
        $a1Result = $results->firstWhere('scholarship.id', $a1->id);
        $this->assertNotNull($a1Result);
        // Raw values checks:
        $this->assertEquals(8, $a1Result['raw_values']['C1']); // semester max - min + 1 = 8 - 1 + 1 = 8
        $this->assertEquals(3, $a1Result['raw_values']['C2']);
        $this->assertEquals(5, $a1Result['raw_values']['C3']); // Surplus IPK = 3.45 - 2.50 = 0.95 >= 0.75 -> score 5
        $this->assertEquals(2, $a1Result['raw_values']['C4']); // Rasio = 3M / 4M = 0.75 (0.65 < 0.75 <= 0.85 -> score 2)

        // Normalized checks:
        $this->assertEquals(1.0, $a1Result['normalized_values']['C1']); // 8 / max(8, 6) = 1.0
        $this->assertEquals(1.0, $a1Result['normalized_values']['C2']); // 3 / max(3, 2) = 1.0
        $this->assertEquals(1.0, $a1Result['normalized_values']['C3']); // 5 / max(5, 2) = 1.0
        $this->assertEquals(1.0, $a1Result['normalized_values']['C4']); // 2 / max(2, 1) = 1.0
        $this->assertEquals(1.000000, $a1Result['nilai_preferensi']);

        // A2 (Unggulan Kemendikbud) checks:
        $a2Result = $results->firstWhere('scholarship.id', $a2->id);
        $this->assertNotNull($a2Result);
        // Raw values checks:
        $this->assertEquals(6, $a2Result['raw_values']['C1']); // semester range = 6
        $this->assertEquals(2, $a2Result['raw_values']['C2']);
        $this->assertEquals(2, $a2Result['raw_values']['C3']); // Surplus IPK = 3.45 - 3.25 = 0.20 (0.10 <= 0.20 < 0.30 -> score 2)
        $this->assertEquals(1, $a2Result['raw_values']['C4']); // Rasio = 3M / 3.5M = 0.85714 (0.85 < 0.857 <= 1.00 -> score 1)

        // Normalized checks:
        $this->assertEquals(0.75, $a2Result['normalized_values']['C1']); // 6 / 8 = 0.75
        $this->assertEquals(2/3, $a2Result['normalized_values']['C2']); // 2 / 3 = 0.666667
        $this->assertEquals(0.40, $a2Result['normalized_values']['C3']); // 2 / 5 = 0.40
        $this->assertEquals(0.50, $a2Result['normalized_values']['C4']); // 1 / 2 = 0.50

        // Preferred Vi checks:
        // Vi = (0.25 * 0.75) + (0.20 * 2/3) + (0.30 * 0.40) + (0.25 * 0.50) = 0.1875 + 0.133333 + 0.1200 + 0.1250 = 0.565833
        $this->assertEquals(round(0.565833, 6), round($a2Result['nilai_preferensi'], 6));

        // Ranking check: A1 should be rank 1, A2 rank 2
        $this->assertEquals(1, $a1Result['peringkat']);
        $this->assertEquals(2, $a2Result['peringkat']);
    }
}
