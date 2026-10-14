<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_diakses(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login');
    }

    public function test_dosen_dapat_login_dan_diarahkan_ke_dashboard(): void
    {
        $dosen = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->post('/login', [
            'email' => $dosen->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($dosen);
    }

    public function test_login_dengan_kredensial_salah_gagal_dengan_pesan_error(): void
    {
        $dosen = User::factory()->create(['password' => Hash::make('password')]);

        $this->from('/login')->post('/login', [
            'email' => $dosen->email,
            'password' => 'password-salah',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_halaman_selain_login_hanya_dapat_diakses_setelah_autentikasi(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/rekap')->assertRedirect('/login');
        $this->get('/mahasiswa')->assertRedirect('/login');
    }

    public function test_dosen_dapat_logout(): void
    {
        $dosen = User::factory()->create();

        $this->actingAs($dosen)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
