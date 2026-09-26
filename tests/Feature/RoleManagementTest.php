<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_petugas_cannot_open_role_management(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('petugas');

        $this->actingAs($user)->get('/roles')->assertForbidden();
    }

    public function test_super_admin_can_view_roles_index(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');

        $this->actingAs($user)->get('/roles')->assertOk();
    }

    public function test_super_admin_can_create_a_role_with_permissions(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post('/roles', [
                'name' => 'pustakawan-magang',
                'permissions' => ['books.view', 'books.create'],
            ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'pustakawan-magang')->firstOrFail();
        $this->assertEqualsCanonicalizing(['books.view', 'books.create'], $role->permissions->pluck('name')->all());

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'PERMISSION_CHANGED',
            'user_id' => $user->id,
        ]);
    }

    public function test_super_admin_can_update_a_roles_permissions(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');
        $role = Role::create(['name' => 'pustakawan-magang', 'guard_name' => 'web']);
        $role->syncPermissions(['books.view']);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put("/roles/{$role->id}", [
                'name' => 'pustakawan-magang',
                'permissions' => ['books.view', 'books.update'],
            ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertEqualsCanonicalizing(['books.view', 'books.update'], $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_super_admins_own_permissions_cannot_be_changed(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');
        $role = Role::where('name', 'super-admin')->firstOrFail();
        $before = $role->permissions->pluck('name')->sort()->values()->all();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put("/roles/{$role->id}", [
                'name' => 'super-admin',
                'permissions' => ['books.view'],
            ]);

        $response->assertRedirect();
        $this->assertEqualsCanonicalizing($before, $role->fresh()->permissions->pluck('name')->sort()->values()->all());
    }

    public function test_protected_role_cannot_be_deleted(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');
        $role = Role::where('name', 'petugas')->firstOrFail();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete("/roles/{$role->id}");

        $response->assertRedirect();
        $this->assertModelExists($role);
    }

    public function test_role_still_in_use_cannot_be_deleted(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');
        $role = Role::create(['name' => 'pustakawan-magang', 'guard_name' => 'web']);
        $this->makeUser('pustakawan-magang');

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete("/roles/{$role->id}");

        $response->assertRedirect();
        $this->assertModelExists($role);
    }

    public function test_unused_custom_role_can_be_deleted(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');
        $role = Role::create(['name' => 'pustakawan-magang', 'guard_name' => 'web']);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete("/roles/{$role->id}");

        $response->assertRedirect(route('roles.index'));
        $this->assertModelMissing($role);
    }

    public function test_permissions_reference_page_is_visible_to_super_admin(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('super-admin');

        $response = $this->actingAs($user)->get('/permissions');

        $response->assertOk();
        $response->assertSee('books.view');
    }

    public function test_anggota_cannot_open_permissions_reference_page(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');

        $this->actingAs($user)->get('/permissions')->assertForbidden();
    }
}
