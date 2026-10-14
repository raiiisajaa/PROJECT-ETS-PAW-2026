<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_awal_mengarahkan_ke_login_bagi_tamu(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_semua_route_utama_terdaftar(): void
    {
        $daftarRoute = [
            'login',
            'logout',
            'dashboard',
            'mata-kuliah.index',
            'komponen.pilih',
            'komponen.index',
            'komponen.simpan',
            'nilai.pilih',
            'nilai.index',
            'nilai.simpan',
            'nilai.import',
            'nilai.template',
            'nilai.detail',
            'nilai.feedback',
            'mahasiswa.index',
            'mahasiswa.store',
            'mahasiswa.update',
            'mahasiswa.destroy',
            'rekap.index',
            'rekap.export',
            'profil',
        ];

        foreach ($daftarRoute as $nama) {
            $this->assertTrue(
                app('router')->has($nama),
                "Route [{$nama}] tidak terdaftar."
            );
        }
    }
}
