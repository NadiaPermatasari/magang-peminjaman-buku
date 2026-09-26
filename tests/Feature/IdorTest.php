<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Models\Fine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

/**
 * Broken Access Control / IDOR (spec §26) — a member must never be able to
 * view another member's loan or fine by guessing/changing the UUID in the
 * URL, and a random UUID must 404 rather than leak existence via a 403.
 */
class IdorTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_member_cannot_view_another_members_loan(): void
    {
        $this->seedRoles();

        $userA = $this->makeUser('anggota');
        $this->makeMember($userA);
        $userB = $this->makeUser('anggota');
        $memberB = $this->makeMember($userB);

        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($memberB, [$book->id]);

        $response = $this->actingAs($userA)->get("/loans/{$loan->uuid}");

        $response->assertForbidden();
    }

    public function test_member_can_view_their_own_loan(): void
    {
        $this->seedRoles();

        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $response = $this->actingAs($user)->get("/loans/{$loan->uuid}");

        $response->assertOk();
    }

    public function test_random_uuid_returns_404_not_403(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $this->makeMember($user);

        $response = $this->actingAs($user)->get('/loans/'.Str::uuid());

        $response->assertNotFound();
    }

    public function test_staff_with_view_all_permission_can_view_any_loan(): void
    {
        $this->seedRoles();

        $petugas = $this->makeUser('petugas');
        $memberUser = $this->makeUser('anggota');
        $member = $this->makeMember($memberUser);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $response = $this->actingAs($petugas)->get("/loans/{$loan->uuid}");

        $response->assertOk();
    }

    public function test_member_cannot_view_another_members_fine(): void
    {
        $this->seedRoles();

        $userA = $this->makeUser('anggota');
        $this->makeMember($userA);

        $userB = $this->makeUser('anggota');
        $memberB = $this->makeMember($userB);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($memberB, [$book->id]);

        Fine::create([
            'loan_item_id' => $loan->items->first()->id,
            'member_id' => $memberB->id,
            'type' => 'LATE_RETURN',
            'late_days' => 2,
            'rate' => 1000,
            'amount' => 123456,
            'status' => 'UNPAID',
            'calculated_at' => now(),
        ]);

        // fines.index scopes by the caller's own member_id when they lack
        // the fines.view permission (FineController::index) — B's fine
        // amount must never appear in A's own list.
        $response = $this->actingAs($userA)->get('/fines');

        $response->assertOk();
        $response->assertDontSee('123.456');
    }
}
