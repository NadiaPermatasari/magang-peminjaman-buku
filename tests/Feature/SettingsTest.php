<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_unauthorized_user_cannot_update_settings(): void
    {
        $this->seedRoles();
        $petugas = $this->makeUser('petugas');

        $response = $this->actingAs($petugas)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put('/settings', ['app_name' => 'Hacked']);

        $response->assertForbidden();
        $this->assertNotSame('Hacked', app_name());
    }

    public function test_super_admin_can_update_settings_and_cache_refreshes(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        // Warm the settings cache with the old value.
        $this->assertNotSame('Perpustakaan Baru', app_name());

        $response = $this->actingAs($admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put('/settings', $this->validSettingsPayload(['app_name' => 'Perpustakaan Baru']));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertSame('Perpustakaan Baru', app_name());
    }

    public function test_settings_edit_page_renders_with_whatsapp_toggle(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $response = $this->actingAs($admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get('/settings');

        $response->assertOk();
        $response->assertSee('whatsapp_notification_enabled', false);
    }

    public function test_settings_update_without_confirmed_password_is_blocked(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $response = $this->actingAs($admin)->put('/settings', $this->validSettingsPayload());

        $response->assertRedirect(route('password.confirm'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validSettingsPayload(array $overrides = []): array
    {
        return array_merge([
            'app_name' => 'Perpustakaan Test',
            'timezone' => 'Asia/Jakarta',
            'per_page' => 10,
            'loan_duration_days' => 7,
            'max_active_loans' => 3,
            'pickup_deadline_days' => 2,
            'max_renewals' => 1,
            'fine_amount_per_day' => 1000,
            'fine_grace_period' => 0,
            'pickup_reminder_hours' => 24,
            'primary_color' => '#5e72e4',
        ], $overrides);
    }
}
