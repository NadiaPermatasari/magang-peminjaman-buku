<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_manifest_is_public_and_contains_installable_defaults(): void
    {
        $response = $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('name', config('app.name'))
            ->assertJsonPath('short_name', config('app.name'))
            ->assertJsonPath('id', '/')
            ->assertJsonPath('scope', '/')
            ->assertJsonPath('start_url', '/dashboard')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('lang', 'id')
            ->assertJsonPath('prefer_related_applications', false);

        foreach ($response->json('icons') as $icon) {
            $path = public_path(parse_url($icon['src'], PHP_URL_PATH));
            $this->assertFileExists($path);
            $image = getimagesize($path);
            $this->assertSame($icon['sizes'], "{$image[0]}x{$image[1]}");
            $this->assertSame('image/png', $image['mime']);
        }

        $appleIcon = getimagesize(public_path('pwa/icons/apple-touch-icon.png'));
        $this->assertSame([180, 180], [$appleIcon[0], $appleIcon[1]]);
    }

    public function test_manifest_uses_current_application_settings(): void
    {
        Setting::setMany([
            'app_name' => 'Peminjaman Berkas Pertanahan',
            'app_short_name' => 'Arsip Tanah',
            'description' => 'Layanan arsip pertanahan kantor.',
            'primary_color' => '#123456',
        ]);

        $this->get('/manifest.webmanifest')
            ->assertJsonPath('name', 'Peminjaman Berkas Pertanahan')
            ->assertJsonPath('short_name', 'Arsip Tanah')
            ->assertJsonPath('description', 'Layanan arsip pertanahan kantor.')
            ->assertJsonPath('theme_color', '#123456');

        Setting::set('app_name', 'Layanan Berkas Baru');

        $this->get('/manifest.webmanifest')->assertJsonPath('name', 'Layanan Berkas Baru');
    }

    public function test_public_login_and_authenticated_layouts_include_pwa_controls(): void
    {
        foreach (['/', '/login'] as $uri) {
            $this->get($uri)->assertOk()
                ->assertSee('rel="manifest"', false)
                ->assertSee(route('pwa.manifest'), false)
                ->assertSee('sizes="180x180"', false)
                ->assertSee('id="pwa-install-button"', false);
        }

        $this->seedRoles();
        $this->actingAs($this->makeUser('anggota'))->get('/dashboard')
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('Pasang Aplikasi')
            ->assertSee('Tambahkan ke Layar Utama');
    }

    public function test_offline_page_is_public_and_independent_of_sessions_and_external_assets(): void
    {
        $this->get('/offline')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Koneksi terputus')
            ->assertSee('Coba Lagi')
            ->assertDontSee('csrf-token', false)
            ->assertDontSee('<script', false)
            ->assertDontSee('<link', false);

        $this->seedRoles();
        $user = $this->makeUser('anggota', ['name' => 'Nama Anggota Rahasia']);

        $this->actingAs($user)->get('/offline')->assertOk()
            ->assertDontSee($user->name)
            ->assertDontSee($user->email)
            ->assertDontSee('csrf-token', false);
    }

    public function test_offline_page_escapes_branding_text(): void
    {
        Setting::set('app_name', '<script>alert("uji")</script>');

        $this->get('/offline')->assertOk()
            ->assertSee(e('<script>alert("uji")</script>'), false)
            ->assertDontSee('<script>', false);
    }

    public function test_pwa_csp_only_allows_same_origin_manifests_and_workers(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("manifest-src 'self'", $csp);
        $this->assertStringContainsString("worker-src 'self'", $csp);
        $this->assertStringContainsString("connect-src 'self'", $csp);
    }
}
