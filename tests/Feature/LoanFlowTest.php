<?php

namespace Tests\Feature;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\ExpireLoan;
use App\Actions\Loans\HandoverLoan;
use App\Actions\Loans\RejectLoan;
use App\Actions\Loans\ReturnLoan;
use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class LoanFlowTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    public function test_creating_a_loan_starts_as_pending_and_does_not_allocate_a_copy(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $this->assertSame(LoanStatus::PENDING, $loan->status);
        $this->assertNull($loan->items->first()->book_copy_id);
        $this->assertSame(BookCopyStatus::AVAILABLE, $book->copies->first()->fresh()->status);
        $this->assertMatchesRegularExpression('/^PJ-\d{8}-\d{6}$/', $loan->code);
    }

    public function test_approving_a_loan_reserves_a_copy(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);

        $this->assertSame(LoanStatus::APPROVED, $loan->status);
        $copy = $loan->items->first()->bookCopy;
        $this->assertSame(BookCopyStatus::RESERVED, $copy->status);
        $this->assertNotNull($loan->pickup_deadline);
    }

    public function test_rejecting_a_loan_requires_no_copy_and_records_reason(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(RejectLoan::class)->handle($loan, $staff, 'Stok diperlukan untuk kelas lain');

        $this->assertSame(LoanStatus::REJECTED, $loan->status);
        $this->assertSame('Stok diperlukan untuk kelas lain', $loan->rejection_reason);
        $this->assertSame(BookCopyStatus::AVAILABLE, $book->copies->first()->fresh()->status);
    }

    public function test_a_book_copy_cannot_be_allocated_to_two_loans_at_once(): void
    {
        $this->seedRoles();
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy(); // only 1 copy

        $memberA = $this->makeMember($this->makeUser('anggota'));
        $memberB = $this->makeMember($this->makeUser('anggota'));

        $loanA = app(CreateLoan::class)->handle($memberA, [$book->id]);
        $loanB = app(CreateLoan::class)->handle($memberB, [$book->id]);

        app(ApproveLoan::class)->handle($loanA, $staff);

        $this->expectException(LoanException::class);
        app(ApproveLoan::class)->handle($loanB, $staff);
    }

    public function test_handover_flips_copy_and_loan_to_borrowed(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);
        $barcode = $loan->items->first()->bookCopy->barcode;

        $loan = app(HandoverLoan::class)->handle($loan, $barcode, $staff);

        $this->assertSame(LoanStatus::BORROWED, $loan->status);
        $this->assertSame(BookCopyStatus::BORROWED, $loan->items->first()->bookCopy->status);
        $this->assertNotNull($loan->due_at);
    }

    public function test_return_flips_copy_back_to_available_and_closes_loan(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);
        $barcode = $loan->items->first()->bookCopy->barcode;
        $loan = app(HandoverLoan::class)->handle($loan, $barcode, $staff);

        $result = app(ReturnLoan::class)->handle($barcode, BookCondition::GOOD, $staff);

        $this->assertSame(LoanStatus::RETURNED, $result['loan']->status);
        $this->assertSame(BookCopyStatus::AVAILABLE, $result['item']->bookCopy->status);
        $this->assertNull($result['fine']);
    }

    public function test_returning_late_creates_a_fine(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        Setting::set('fine_amount_per_day', 1000);
        Setting::set('fine_grace_period', 0);
        Setting::set('fine_enabled', true);

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);
        $barcode = $loan->items->first()->bookCopy->barcode;
        $loan = app(HandoverLoan::class)->handle($loan, $barcode, $staff);

        // Backdate due_at to simulate 3 late days.
        $loan->items()->update(['due_at' => now()->subDays(3)]);

        $result = app(ReturnLoan::class)->handle($barcode, BookCondition::GOOD, $staff);

        $this->assertNotNull($result['fine']);
        $this->assertSame(3, $result['fine']->late_days);
        $this->assertSame(3000, $result['fine']->amount);
    }

    public function test_expiring_an_approved_loan_releases_the_copy(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);
        $loan->update(['pickup_deadline' => now()->subDay()]);

        $loan = app(ExpireLoan::class)->handle($loan->fresh());

        $this->assertSame(LoanStatus::EXPIRED, $loan->status);
        $this->assertSame(BookCopyStatus::AVAILABLE, $loan->items->first()->bookCopy->fresh()->status);
    }

    public function test_illegal_status_transition_is_rejected(): void
    {
        $this->seedRoles();
        $member = $this->makeMember($this->makeUser('anggota'));
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        app(RejectLoan::class)->handle($loan, $staff, 'Alasan');

        $this->expectException(LoanException::class);
        app(ApproveLoan::class)->handle($loan->fresh(), $staff);
    }
}
