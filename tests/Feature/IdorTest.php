<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Enums\ExtensionStatus;
use App\Models\LoanExtension;
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

    public function test_member_cannot_view_another_members_extension_request(): void
    {
        $this->seedRoles();

        $userA = $this->makeUser('anggota');
        $this->makeMember($userA);

        $userB = $this->makeUser('anggota');
        $memberB = $this->makeMember($userB);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($memberB, [$book->id]);

        LoanExtension::create([
            'loan_id' => $loan->id,
            'requested_by' => $userB->id,
            'days' => 5,
            'reason' => 'Alasan rahasia milik anggota B',
            'status' => ExtensionStatus::PENDING,
            'requested_at' => now(),
        ]);

        // loan-extensions.index scopes to the caller's own member when they
        // lack loan-extensions.view (LoanExtensionController::index) — B's
        // request must never appear in A's list.
        $response = $this->actingAs($userA)->get('/loan-extensions');

        $response->assertOk();
        $response->assertDontSee('Alasan rahasia milik anggota B');
    }
}
