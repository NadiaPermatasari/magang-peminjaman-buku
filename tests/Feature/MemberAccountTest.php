<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class MemberAccountTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Peminjam',
            'email' => 'budi.peminjam@example.test',
            'phone' => '08123456789',
            'address' => 'Jl. Mawar 1',
            'identity_number' => '3201010101010001',
            'status' => 'ACTIVE',
            'joined_at' => now()->toDateString(),
            'create_login' => '1',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $overrides);
    }

    public function test_staff_can_create_a_member_with_a_usable_login(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');

        $this->actingAs($admin)->post('/members', $this->payload())
            ->assertRedirect(route('members.index'));

        $member = Member::firstOrFail();
        $this->assertNotNull($member->user_id);

        $user = $member->user;
        $this->assertTrue($user->hasRole('anggota'));
        // Email langsung terverifikasi supaya anggota bisa login tanpa
        // menunggu email (server email opsional di lingkungan ini).
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_the_created_member_can_actually_sign_in(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');
        $this->actingAs($admin)->post('/members', $this->payload());

        // Keluar dari sesi admin sebelum mencoba login sebagai anggota.
        $this->post('/logout');
        $this->flushSession();

        $response = $this->post('/login', [
            'email' => 'budi.peminjam@example.test',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'budi.peminjam@example.test')->firstOrFail());
    }

    public function test_creating_a_login_requires_email_and_password(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');

        $this->actingAs($admin)
            ->post('/members', $this->payload(['email' => '', 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertSame(0, Member::count());
    }

    public function test_member_can_be_created_without_a_login_account(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');

        $this->actingAs($admin)
            ->post('/members', $this->payload(['create_login' => '0', 'password' => '', 'password_confirmation' => '']))
            ->assertRedirect(route('members.index'));

        $this->assertNull(Member::firstOrFail()->user_id);
        $this->assertSame(0, User::where('email', 'budi.peminjam@example.test')->count());
    }

    public function test_staff_can_add_a_login_to_an_existing_member_later(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');
        $member = $this->makeMember(null, ['email' => 'rina@example.test']);

        $this->actingAs($admin)->post("/members/{$member->uuid}/account", [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect();

        $member = $member->fresh('user');
        $this->assertNotNull($member->user);
        $this->assertTrue($member->user->hasRole('anggota'));
        $this->assertTrue(Hash::check('Password123!', $member->user->password));
    }

    public function test_staff_can_reset_an_existing_member_password(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');
        $anggota = $this->makeUser('anggota');
        $member = $this->makeMember($anggota);

        $this->actingAs($admin)->post("/members/{$member->uuid}/account", [
            'password' => 'KataSandiBaru123!',
            'password_confirmation' => 'KataSandiBaru123!',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('KataSandiBaru123!', $anggota->fresh()->password));
    }

    public function test_an_account_cannot_be_created_for_a_member_without_an_email(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin-perpustakaan');
        $member = $this->makeMember(null, ['email' => null]);

        $this->actingAs($admin)->post("/members/{$member->uuid}/account", [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('password');

        $this->assertNull($member->fresh()->user_id);
    }

    public function test_anggota_cannot_manage_member_accounts(): void
    {
        $this->seedRoles();
        $anggota = $this->makeUser('anggota');
        $member = $this->makeMember(null, ['email' => 'rina@example.test']);

        $this->actingAs($anggota)->post("/members/{$member->uuid}/account", [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertForbidden();
    }
}
