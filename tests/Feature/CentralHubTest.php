<?php

namespace Tests\Feature;

use App\Models\KlienSekolah;
use App\Models\Lisensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentralHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_are_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->get('/pricing');
        $response->assertStatus(200);

        $response = $this->get('/verify');
        $response->assertStatus(200);

        $response = $this->get('/demo');
        $response->assertStatus(200);

        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_npsn_verification_api(): void
    {
        $response = $this->getJson('/api/v1/license/verify/20104050');
        $response->assertStatus(200)
            ->assertJson([
                'verified' => true,
                'npsn' => '20104050',
                'nama_sekolah' => 'SMK Negeri 1 Aksara Nusantara',
                'model_lisensi' => 'Beli Putus On-Premise',
            ]);
    }

    public function test_instant_demo_lead_api(): void
    {
        $payload = [
            'nama_pemohon' => 'Bambang Trianto',
            'nama_sekolah' => 'SMK Negeri 3 Surabaya',
            'tipe_sekolah' => 'smk',
            'nomor_wa' => '081299887711',
            'email' => 'admin@smkn3sby.sch.id',
            'estimasi_siswa' => 850,
            'model_minat' => 'beli_putus',
        ];

        $response = $this->postJson('/api/v1/leads/demo', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'demo_url', 'expired_at', 'username_demo', 'password_demo'],
            ]);
    }

    public function test_heartbeat_telemetry_api(): void
    {
        $lisensi = Lisensi::where('nomor_lisensi', 'LIC-2026-SMA-20205060')->first();

        $payload = [
            'npsn' => '20205060',
            'versi_lms' => '1.0.0',
            'versi_php' => '8.3.6',
            'metrik' => [
                'total_siswa' => 650,
                'total_guru' => 45,
                'total_rombel' => 18,
                'total_ujian_cbt' => 120,
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $lisensi->token_api)
            ->postJson('/api/v1/license/heartbeat', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'active',
            ]);
    }

    public function test_update_registry_check_api(): void
    {
        $response = $this->getJson('/api/v1/updates/check?current_version=1.0.0');
        $response->assertStatus(200)
            ->assertJson([
                'update_available' => true,
                'version' => '1.0.1',
            ]);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');

        $user = User::where('email', 'admin@aksaraedu.id')->first();
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_deploy_webhook_rejects_invalid_token(): void
    {
        config(['app.deploy_webhook_secret' => 'TestSecretKey123']);

        $response = $this->withHeader('X-Deploy-Token', 'WrongKey')
            ->postJson('/api/deploy-webhook', ['token' => 'WrongKey']);

        $response->assertStatus(403);
    }

    public function test_deploy_webhook_accepts_valid_token(): void
    {
        config(['app.deploy_webhook_secret' => 'TestSecretKey123']);

        $response = $this->withHeader('X-Deploy-Token', 'TestSecretKey123')
            ->postJson('/api/deploy-webhook', ['token' => 'TestSecretKey123']);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}
