<?php

namespace Tests\Feature;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\HandoverLoan;
use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

/**
 * Bukti foto serah terima (Peminjaman Aktif) dan bukti foto pengembalian
 * yang menggantikan scan barcode.
 */
class LoanProofPhotoTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function approvedLoan(&$staff = null): Loan
    {
        $staff = $this->makeUser('petugas');
        $member = $this->makeMember($this->makeUser('anggota'));
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        return app(ApproveLoan::class)->handle($loan, $staff);
    }

    public function test_handover_stores_the_proof_photo(): void
    {
        $this->seedRoles();
        $loan = $this->approvedLoan($staff);
        $barcode = $loan->items->first()->bookCopy->barcode;

        $this->actingAs($staff)->post("/loans/{$loan->uuid}/handover", [
            'barcode' => $barcode,
            'photo' => UploadedFile::fake()->image('serah-terima.jpg'),
        ])->assertRedirect();

        $item = $loan->fresh('items')->items->first();

        $this->assertSame(LoanStatus::BORROWED, $item->status);
        $this->assertNotNull($item->handover_photo_path);
        Storage::disk('public')->assertExists($item->handover_photo_path);
    }

    public function test_handover_still_works_without_a_photo_and_it_can_be_added_later(): void
    {
        $this->seedRoles();
        $loan = $this->approvedLoan($staff);
        $barcode = $loan->items->first()->bookCopy->barcode;

        $this->actingAs($staff)->post("/loans/{$loan->uuid}/handover", ['barcode' => $barcode])
            ->assertRedirect();

        $item = $loan->fresh('items')->items->first();
        $this->assertNull($item->handover_photo_path);

        // Diunggah menyusul dari halaman Peminjaman Aktif.
        $this->actingAs($staff)->post("/loans/{$loan->uuid}/items/{$item->uuid}/photo", [
            'photo' => UploadedFile::fake()->image('serah-terima.jpg'),
        ])->assertRedirect();

        $item = $item->fresh();
        $this->assertNotNull($item->handover_photo_path);
        Storage::disk('public')->assertExists($item->handover_photo_path);
    }

    public function test_a_non_image_upload_is_rejected_as_handover_proof(): void
    {
        $this->seedRoles();
        $loan = $this->approvedLoan($staff);
        $barcode = $loan->items->first()->bookCopy->barcode;

        $this->actingAs($staff)->post("/loans/{$loan->uuid}/handover", [
            'barcode' => $barcode,
            'photo' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo "pwned"; ?>'),
        ])->assertSessionHasErrors('photo');

        $this->assertSame(LoanStatus::APPROVED, $loan->fresh()->status);
    }

    public function test_the_photo_upload_route_rejects_an_item_from_another_loan(): void
    {
        $this->seedRoles();
        $loanA = $this->approvedLoan($staff);
        $loanB = $this->approvedLoan($other);

        $this->actingAs($staff)->post("/loans/{$loanA->uuid}/items/{$loanB->items->first()->uuid}/photo", [
            'photo' => UploadedFile::fake()->image('serah-terima.jpg'),
        ])->assertNotFound();
    }

    public function test_return_is_processed_from_the_item_plus_a_mandatory_photo(): void
    {
        $this->seedRoles();
        $loan = $this->approvedLoan($staff);
        $barcode = $loan->items->first()->bookCopy->barcode;
        $loan = app(HandoverLoan::class)->handle($loan, $barcode, $staff);
        $item = $loan->items->first();

        $this->actingAs($staff)->get('/returns')->assertOk()->assertSee($loan->code);

        $this->actingAs($staff)->post('/returns', [
            'loan_item_id' => $item->id,
            'condition' => 'GOOD',
            'photo' => UploadedFile::fake()->image('pengembalian.jpg'),
        ])->assertRedirect();

        $item = $item->fresh();

        $this->assertSame(LoanStatus::RETURNED, $item->status);
        $this->assertSame(LoanStatus::RETURNED, $loan->fresh()->status);
        $this->assertSame(BookCopyStatus::AVAILABLE, $item->bookCopy->status);
        Storage::disk('public')->assertExists($item->return_photo_path);
    }

    public function test_return_without_a_photo_is_rejected(): void
    {
        $this->seedRoles();
        $loan = $this->approvedLoan($staff);
        $barcode = $loan->items->first()->bookCopy->barcode;
        $loan = app(HandoverLoan::class)->handle($loan, $barcode, $staff);
        $item = $loan->items->first();

        $this->actingAs($staff)->post('/returns', [
            'loan_item_id' => $item->id,
            'condition' => 'GOOD',
        ])->assertSessionHasErrors('photo');

        $this->assertSame(LoanStatus::BORROWED, $item->fresh()->status);
    }

    public function test_return_rejects_an_item_that_is_not_currently_borrowed(): void
    {
        $this->seedRoles();
        $loan = $this->approvedLoan($staff);

        // Masih APPROVED (belum diserahkan) — tidak boleh bisa dikembalikan.
        $this->actingAs($staff)->post('/returns', [
            'loan_item_id' => $loan->items->first()->id,
            'condition' => 'GOOD',
            'photo' => UploadedFile::fake()->image('pengembalian.jpg'),
        ])->assertSessionHasErrors('loan_item_id');
    }

    public function test_anggota_cannot_process_returns(): void
    {
        $this->seedRoles();
        $anggota = $this->makeUser('anggota');

        $this->actingAs($anggota)->get('/returns')->assertForbidden();
    }
}
