<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_anggota_cannot_open_user_management(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');

        $response = $this->actingAs($user)->get('/users');

        $response->assertForbidden();
    }

    public function test_petugas_cannot_manage_roles_or_users(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('petugas');

        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->get('/settings')->assertForbidden();
    }

    public function test_super_admin_can_manage_settings(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');

        // settings.edit requires password.confirm (spec §6); seed a fresh
        // confirmation timestamp into the test session so it isn't blocked.
        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get('/settings');

        $response->assertOk();
    }

    public function test_pimpinan_is_read_only_and_cannot_reach_admin_screens(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('pimpinan');

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/reports/statistics')->assertOk();

        // No management permissions at all.
        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->get('/books/create')->assertForbidden();
        $this->actingAs($user)->get('/members/create')->assertForbidden();
    }

    public function test_role_check_is_permission_driven_not_hardcoded_string(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');

        // admin-perpustakaan has books.create but not roles.manage / settings.manage.
        $this->actingAs($admin)->get('/books/create')->assertOk();
        $this->actingAs($admin)->get('/settings')->assertForbidden();
    }
}
