<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

/**
 * Halaman "Pengajuan" dulu membalas 403 untuk setiap akun yang tidak
 * terhubung ke data anggota — termasuk admin dan petugas, yang justru
 * paling sering membukanya.
 */
class LoanRequestAccessTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_staff_can_open_the_loan_request_list(): void
    {
        $this->seedRoles();

        foreach (['super-admin', 'admin-perpustakaan', 'petugas'] as $role) {
            $this->actingAs($this->makeUser($role))->get('/loans')->assertOk();
        }
    }

    public function test_staff_see_every_request_while_a_member_only_sees_their_own(): void
    {
        $this->seedRoles();

        $userA = $this->makeUser('anggota');
        $memberA = $this->makeMember($userA);
        $userB = $this->makeUser('anggota');
        $memberB = $this->makeMember($userB);
        $book = $this->makeBookWithCopy();

        $loanA = app(CreateLoan::class)->handle($memberA, [$book->id]);
        $loanB = app(CreateLoan::class)->handle($memberB, [$book->id]);

        $this->actingAs($this->makeUser('petugas'))->get('/loans')
            ->assertOk()
            ->assertSee($loanA->code)
            ->assertSee($loanB->code);

        $this->actingAs($userA)->get('/loans')
            ->assertOk()
            ->assertSee($loanA->code)
            ->assertDontSee($loanB->code);
    }

    public function test_staff_can_open_the_create_form_and_submit_on_behalf_of_a_member(): void
    {
        $this->seedRoles();
        $staff = $this->makeUser('petugas');
        $member = $this->makeMember($this->makeUser('anggota'));
        $book = $this->makeBookWithCopy();

        $this->actingAs($staff)->get('/loans/create')->assertOk();

        $this->actingAs($staff)->post('/loans', [
            'member_id' => $member->id,
            'book_ids' => [$book->id],
        ])->assertRedirect();

        $loan = Loan::firstOrFail();
        $this->assertSame($member->id, $loan->member_id);
        $this->assertSame(LoanStatus::PENDING, $loan->status);
    }

    public function test_staff_must_pick_a_member_when_submitting_on_behalf(): void
    {
        $this->seedRoles();
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $this->actingAs($staff)->post('/loans', ['book_ids' => [$book->id]])
            ->assertSessionHasErrors('member_id');

        $this->assertSame(0, Loan::count());
    }

    public function test_a_member_submitting_for_themselves_cannot_be_overridden_by_member_id(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('anggota');
        $own = $this->makeMember($user);
        $other = $this->makeMember($this->makeUser('anggota'));
        $book = $this->makeBookWithCopy();

        $this->actingAs($user)->post('/loans', [
            // Dicoba mengajukan atas nama anggota lain.
            'member_id' => $other->id,
            'book_ids' => [$book->id],
        ])->assertRedirect();

        $this->assertSame($own->id, Loan::firstOrFail()->member_id);
    }

    public function test_an_account_without_a_member_record_and_without_staff_rights_is_redirected_not_forbidden(): void
    {
        $this->seedRoles();
        // Anggota tanpa data anggota: diarahkan dengan pesan, bukan 403.
        $orphan = $this->makeUser('anggota');

        $this->actingAs($orphan)->get('/loans')->assertRedirect(route('dashboard'));
        $this->actingAs($orphan)->get('/loans/create')->assertRedirect(route('dashboard'));
    }
}
