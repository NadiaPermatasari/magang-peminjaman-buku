<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota', ['password' => bcrypt('correct-password')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota', ['password' => bcrypt('correct-password')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_protected_page_requires_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_staff_role_without_two_factor_can_still_reach_dashboard(): void
    {
        // Product decision: 2FA is optional for every role (no role is
        // forced into it), unlike the spec §6 default this project
        // otherwise follows — see docs/security.md.
        $this->seedRoles();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('petugas');
        // No 2FA confirmed, and none is required.

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_staff_role_with_two_factor_confirmed_can_reach_dashboard(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('petugas');
        $user->forceFill([
            'two_factor_secret' => encrypt('TESTSECRETKEYFORTOTP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['aaaa-1111', 'bbbb-2222'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_anggota_role_does_not_require_two_factor(): void
    {
        $this->seedRoles();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('anggota');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }
}
