<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Lisensi;
use App\Models\User;
use App\Services\BundleCustomizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionLoaderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Lisensi $lisensi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@aksaraedu.id')->firstOrFail();
        $this->lisensi = Lisensi::with('klienSekolah')->firstOrFail();
    }

    public function test_admin_can_download_web_loader_script(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/lisensi/{$this->lisensi->id}/download-loader");

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="aksara-loader.php"');

        $content = $response->getContent();
        $this->assertStringContainsString('AksaraEdu LMS - Single-File Web Bootstrap Loader', $content);
        $this->assertStringContainsString($this->lisensi->klienSekolah->nama_sekolah, $content);
        $this->assertStringContainsString($this->lisensi->nomor_lisensi, $content);
        $this->assertStringContainsString('Solusi 1 (Root Proxy)', $content);
        $this->assertStringContainsString('aksara_extract_and_setup', $content);
    }

    public function test_client_server_can_download_custom_bundle_via_valid_token(): void
    {
        $customizer = app(BundleCustomizerService::class);
        $token = $customizer->generateProvisionToken($this->lisensi, 48);

        $response = $this->get("/api/v1/provision/download-bundle/{$token}");

        $response->assertStatus(200);
        $this->assertStringContainsString('zip', $response->headers->get('Content-Type') ?? '');
    }

    public function test_client_server_cannot_download_with_invalid_or_tampered_token(): void
    {
        $invalidToken = 'invalid.tampered_token_signature';

        $response = $this->get("/api/v1/provision/download-bundle/{$invalidToken}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonFragment([
            'error' => 'Token provisi tidak sah, salah tanda tangan, atau sudah kedaluwarsa (48 jam). Mohon unduh kembali berkas loader dari portal Central Hub.',
        ]);
    }
}
