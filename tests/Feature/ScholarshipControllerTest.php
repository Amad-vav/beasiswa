<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Criteria;
use App\Models\Weighting;
use App\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScholarshipControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
        Scholarship::create([
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

        Scholarship::create([
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

        Scholarship::create([
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
    }

    public function test_dashboard_index_view_is_accessible()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => 'password',
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('ScholarMatch');
    }

    public function test_calculate_ajax_endpoint_logs_recommendations_to_database_and_returns_json()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => 'password',
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        $payload = [
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ];

        $response = $this->actingAs($user)->postJson('/calculate', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'user',
            'recommendations',
            'ineligible',
            'premium_scholarships'
        ]);

        // Check if user was registered/updated in database
        $this->assertDatabaseHas('users', [
            'email' => 'andi@example.com',
            'nama_lengkap' => 'Andi Pratama',
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        // Check if calculated recommendations (A1, A2) were logged to database
        $this->assertDatabaseCount('recommendations', 2);
        $this->assertDatabaseHas('recommendations', [
            'user_id' => $user->id,
            'peringkat' => 1,
            'nilai_preferensi' => 1.000000,
        ]);

        // Consecutively posting with changed parameters updates user and replaces logged recommendations
        $updatedPayload = [
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'semester' => 5,
            'ipk' => 3.90, // higher IPK
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ];

        $secondResponse = $this->actingAs($user)->postJson('/calculate', $updatedPayload);
        $secondResponse->assertStatus(200);

        // User profile should be updated
        $this->assertDatabaseHas('users', [
            'email' => 'andi@example.com',
            'ipk' => 3.90,
        ]);

        // Recommendations should still be exactly 2 logged (previous deleted)
        $this->assertDatabaseCount('recommendations', 2);
    }

    public function test_unique_click_tracking_endpoint_logs_clicks_and_increments_counter()
    {
        // Setup User and Scholarship
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => 'password',
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        $scholarship = Scholarship::where('nama_beasiswa', 'like', 'A1%')->first();

        // 1. Initial click tracking call (first click = unique click)
        $response = $this->actingAs($user)->postJson("/scholarship/{$scholarship->id}/go", [
            'email' => 'andi@example.com'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('jumlah_klik', 1);

        $this->assertDatabaseHas('click_logs', [
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
        ]);

        $this->assertEquals(1, $scholarship->refresh()->jumlah_klik);

        // 2. Second click tracking call (consecutive click from same user = NOT unique)
        $secondResponse = $this->actingAs($user)->postJson("/scholarship/{$scholarship->id}/go", [
            'email' => 'andi@example.com'
        ]);

        $secondResponse->assertStatus(200);
        $secondResponse->assertJsonPath('success', true);
        $secondResponse->assertJsonPath('jumlah_klik', 1); // should remain 1, not increment to 2

        $this->assertDatabaseCount('click_logs', 1);
        $this->assertEquals(1, $scholarship->refresh()->jumlah_klik);
    }

    public function test_admin_update_weights_configures_weighting_table_and_validates_sum()
    {
        $admin = User::create([
            'nama_lengkap' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'semester' => 7,
            'ipk' => 3.80,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 5000000,
            'is_admin' => true,
        ]);

        $weightings = Weighting::all();
        $payload = [];
        foreach ($weightings as $w) {
            $payload[$w->id] = 0.25; // distribute evenly to sum to 1.0 (0.25 * 4 = 1.0)
        }

        // 1. Valid payload (sum = 1.0)
        $response = $this->actingAs($admin)->post('/admin/weights', [
            'weights' => $payload
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Bobot kriteria SPK berhasil diperbarui!');

        $this->assertDatabaseHas('weighting', [
            'id' => $weightings->first()->id,
            'bobot' => 0.25,
            'versi_bobot' => 2,
        ]);

        // 2. Invalid payload (sum != 1.0, e.g. sum = 0.8)
        $invalidPayload = $payload;
        $invalidPayload[$weightings->first()->id] = 0.05; // 0.05 + 0.25 + 0.25 + 0.25 = 0.80

        $secondResponse = $this->actingAs($admin)->post('/admin/weights', [
            'weights' => $invalidPayload
        ]);

        $secondResponse->assertRedirect();
        $secondResponse->assertSessionHas('error'); // should flash error message
    }
}
