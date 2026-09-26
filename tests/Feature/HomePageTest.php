<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_guest_sees_the_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Masuk', false);
    }

    public function test_authenticated_user_is_redirected_to_dashboard(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_robots_txt_and_sitemap_are_served(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', false);
    }
}
