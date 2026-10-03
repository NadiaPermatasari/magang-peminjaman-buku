<?php

namespace Tests\Feature;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\ApproveLoanExtension;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\HandoverLoan;
use App\Actions\Loans\RejectLoanExtension;
use App\Actions\Loans\RequestLoanExtension;
use App\Enums\ExtensionStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\Loan;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class LoanExtensionTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    /** Peminjaman yang sudah di tangan anggota (BORROWED). */
    private function borrowedLoan(&$user = null, &$staff = null): Loan
    {
        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user);
        $staff = $this->makeUser('petugas');
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);

        return app(HandoverLoan::class)->handle($loan, $loan->items->first()->bookCopy->barcode, $staff);
    }

    public function test_member_can_request_an_extension_for_an_active_loan(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);

        $extension = app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');

        $this->assertSame(ExtensionStatus::PENDING, $extension->status);
        $this->assertSame(3, $extension->days);
        // Jatuh tempo belum berubah sebelum disetujui.
        $this->assertTrue($loan->fresh()->due_at->equalTo($extension->previous_due_at));
    }

    public function test_a_second_pending_request_is_rejected(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);

        app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');

        $this->expectException(LoanException::class);

        app(RequestLoanExtension::class)->handle($loan, $user, 2, 'Sekali lagi');
    }

    public function test_extension_cannot_be_requested_when_renewal_is_disabled(): void
    {
        $this->seedRoles();
        Setting::set('allow_renewal', false);
        $loan = $this->borrowedLoan($user, $staff);

        $this->expectException(LoanException::class);

        app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');
    }

    public function test_approval_shifts_the_due_date_of_the_loan_and_its_items(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);
        $originalDueAt = $loan->due_at->copy();

        $extension = app(RequestLoanExtension::class)->handle($loan, $user, 4, 'Belum selesai membaca');
        $extension = app(ApproveLoanExtension::class)->handle($extension, $staff);

        $loan = $loan->fresh('items');

        $this->assertSame(ExtensionStatus::APPROVED, $extension->status);
        $this->assertSame(4, (int) $originalDueAt->diffInDays($loan->due_at));
        $this->assertTrue($loan->items->first()->due_at->equalTo($loan->due_at));
        $this->assertSame($staff->id, $extension->decided_by);
    }

    public function test_approving_an_overdue_loan_puts_it_back_to_borrowed(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);

        // Simulasikan peminjaman yang sudah telat.
        $loan->update(['status' => LoanStatus::OVERDUE, 'due_at' => now()->subDays(5)]);
        $loan->items()->update(['status' => LoanStatus::OVERDUE, 'due_at' => now()->subDays(5)]);

        $extension = app(RequestLoanExtension::class)->handle($loan->fresh(), $user, 3, 'Sakit, minta perpanjangan');
        $extension = app(ApproveLoanExtension::class)->handle($extension, $staff);

        $loan = $loan->fresh('items');

        $this->assertSame(LoanStatus::BORROWED, $loan->status);
        $this->assertSame(LoanStatus::BORROWED, $loan->items->first()->status);
        // Dihitung dari hari ini, bukan dari jatuh tempo lama yang sudah lewat.
        $this->assertTrue($loan->due_at->isFuture());
        $this->assertNotNull($extension->new_due_at);
    }

    public function test_rejection_keeps_the_due_date_and_records_the_reason(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);
        $originalDueAt = $loan->due_at->copy();

        $extension = app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');
        $extension = app(RejectLoanExtension::class)->handle($extension, $staff, 'Buku sudah dibooking anggota lain');

        $this->assertSame(ExtensionStatus::REJECTED, $extension->status);
        $this->assertSame('Buku sudah dibooking anggota lain', $extension->decision_note);
        $this->assertTrue($loan->fresh()->due_at->equalTo($originalDueAt));
    }

    public function test_max_renewals_limit_is_enforced(): void
    {
        $this->seedRoles();
        Setting::set('max_renewals', 1);
        $loan = $this->borrowedLoan($user, $staff);

        $first = app(RequestLoanExtension::class)->handle($loan, $user, 2, 'Perpanjangan pertama');
        app(ApproveLoanExtension::class)->handle($first, $staff);

        $this->expectException(LoanException::class);

        app(RequestLoanExtension::class)->handle($loan->fresh(), $user, 2, 'Perpanjangan kedua');
    }

    public function test_only_staff_with_the_permission_can_decide(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);
        $extension = app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');

        // Anggota tidak boleh menyetujui pengajuannya sendiri.
        $this->actingAs($user)
            ->post("/loan-extensions/{$extension->uuid}/approve")
            ->assertForbidden();

        $this->actingAs($staff)
            ->post("/loan-extensions/{$extension->uuid}/approve")
            ->assertRedirect();

        $this->assertSame(ExtensionStatus::APPROVED, $extension->fresh()->status);
    }

    public function test_rejection_through_http_requires_a_reason(): void
    {
        $this->seedRoles();
        $loan = $this->borrowedLoan($user, $staff);
        $extension = app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');

        $this->actingAs($staff)
            ->post("/loan-extensions/{$extension->uuid}/reject", [])
            ->assertSessionHasErrors('note');

        $this->assertSame(ExtensionStatus::PENDING, $extension->fresh()->status);
    }
}
