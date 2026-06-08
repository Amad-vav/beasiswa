<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Criteria;
use App\Models\Weighting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $responseAdmin = $this->get('/admin/scholarships');
        $responseAdmin->assertRedirect('/login');

        $responseCalc = $this->post('/calculate', []);
        $responseCalc->assertRedirect('/login');
    }

    public function test_login_renders_correctly()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ScholarMatch');
        $response->assertSee('Masuk ke Akun');
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
            'is_premium' => false,
            'is_admin' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'andi@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_redirected_to_admin_panel_on_login()
    {
        $admin = User::create([
            'nama_lengkap' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'semester' => 7,
            'ipk' => 3.80,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 5000000,
            'is_premium' => true,
            'is_admin' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/scholarships');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        $response = $this->post('/login', [
            'email' => 'andi@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_regular_user_cannot_access_admin_panel()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
            'is_premium' => false,
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin/scholarships');
        $response->assertStatus(403);
    }

    public function test_premium_user_cannot_access_admin_panel()
    {
        $premium = User::create([
            'nama_lengkap' => 'Budi Premium',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.60,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 4000000,
            'is_premium' => true,
            'is_admin' => false,
        ]);

        $response = $this->actingAs($premium)->get('/admin/scholarships');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_panel()
    {
        $admin = User::create([
            'nama_lengkap' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'semester' => 7,
            'ipk' => 3.80,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 5000000,
            'is_premium' => true,
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/scholarships');
        $response->assertStatus(200);
    }

    public function test_user_can_logout()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_access_scholarships_catalog_and_filter()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        $response = $this->actingAs($user)->get('/scholarships?search=KIP');
        $response->assertStatus(200);
        $response->assertSee('Katalog Alternatif');
    }

    public function test_user_can_access_premium_plan_page()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
        ]);

        $response = $this->actingAs($user)->get('/premium');
        $response->assertStatus(200);
        $response->assertSee('Pricing Plans');
    }

    public function test_user_can_upgrade_to_premium()
    {
        $user = User::create([
            'nama_lengkap' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'password' => Hash::make('password123'),
            'semester' => 5,
            'ipk' => 3.45,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 3000000,
            'is_premium' => false,
        ]);

        $response = $this->actingAs($user)->post('/premium/upgrade');
        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertTrue($user->fresh()->is_premium);
    }

    public function test_admin_is_redirected_to_admin_dashboard_from_root()
    {
        $admin = User::create([
            'nama_lengkap' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'semester' => 7,
            'ipk' => 3.80,
            'status_akademik' => 'Aktif Regular',
            'penghasilan_ortu' => 5000000,
            'is_premium' => true,
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/');
        $response->assertRedirect('/admin/scholarships');
    }
}
