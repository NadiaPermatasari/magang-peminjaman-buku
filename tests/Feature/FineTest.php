<?php

namespace Tests\Feature;

use App\Actions\Fines\MarkFinePaid;
use App\Actions\Fines\WaiveFine;
use App\Actions\Loans\CreateLoan;
use App\Enums\FineStatus;
use App\Exceptions\LoanException;
use App\Models\Fine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

class FineTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    private function makeUnpaidFine(): Fine
    {
        $member = $this->makeMember($this->makeUser('anggota'));
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        return Fine::create([
            'loan_item_id' => $loan->items->first()->id,
            'member_id' => $member->id,
            'type' => 'LATE_RETURN',
            'late_days' => 4,
            'rate' => 1000,
            'amount' => 4000,
            'status' => FineStatus::UNPAID,
            'calculated_at' => now(),
        ]);
    }

    public function test_mark_fine_paid_creates_a_payment_and_flips_status(): void
    {
        $this->seedRoles();
        $staff = $this->makeUser('petugas');
        $fine = $this->makeUnpaidFine();

        $fine = app(MarkFinePaid::class)->handle($fine, $staff, 'cash');

        $this->assertSame(FineStatus::PAID, $fine->status);
        $this->assertNotNull($fine->paid_at);
        $this->assertSame(1, $fine->payments()->count());
        $this->assertSame(4000, $fine->payments()->first()->amount);
    }

    public function test_waiving_a_fine_via_http_requires_the_waive_permission(): void
    {
        $this->seedRoles();
        // admin-perpustakaan has fines.mark-paid but not fines.waive (spec §58 "restricted").
        $admin = $this->makeUser('admin-perpustakaan');
        $fine = $this->makeUnpaidFine();

        $response = $this->actingAs($admin)->post("/fines/{$fine->uuid}/waive", [
            'reason' => 'Kondisi force majeure',
        ]);

        $response->assertForbidden();
        $this->assertSame(FineStatus::UNPAID, $fine->fresh()->status);
    }

    public function test_super_admin_can_waive_a_fine_with_reason(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');
        $fine = $this->makeUnpaidFine();

        $fine = app(WaiveFine::class)->handle($fine, $admin, 'Kerusakan sistem internal');

        $this->assertSame(FineStatus::WAIVED, $fine->status);
        $this->assertSame('Kerusakan sistem internal', $fine->waive_reason);
        $this->assertSame($admin->id, $fine->waived_by);
    }

    public function test_waive_reason_is_required_at_the_http_layer(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');
        $fine = $this->makeUnpaidFine();

        $response = $this->actingAs($admin)->post("/fines/{$fine->uuid}/waive", [
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertSame(FineStatus::UNPAID, $fine->fresh()->status);
    }

    public function test_waiving_an_already_paid_fine_is_rejected(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');
        $fine = $this->makeUnpaidFine();
        app(MarkFinePaid::class)->handle($fine, $admin);

        $this->expectException(LoanException::class);
        app(WaiveFine::class)->handle($fine->fresh(), $admin, 'Terlambat mengajukan');
    }
}
