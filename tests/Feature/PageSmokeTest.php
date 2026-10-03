<?php

namespace Tests\Feature;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\HandoverLoan;
use App\Actions\Loans\RequestLoanExtension;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\WithLibraryData;
use Tests\TestCase;

/**
 * Setiap halaman harus tetap bisa dirender. Murah tapi menangkap error
 * Blade/relasi yang lolos dari test per-fitur (mis. kolom master data yang
 * sudah dihapus tapi masih dipanggil di view).
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase, WithLibraryData;

    /**
     * @return array<int, string>
     */
    public static function pageProvider(): array
    {
        return [
            ['dashboard'],
            ['catalog'],
            ['notifications'],
            ['profile'],
            ['books'],
            ['books/create'],
            ['book-copies'],
            ['book-copies/create'],
            ['categories'],
            ['categories/create'],
            ['racks'],
            ['racks/create'],
            ['members'],
            ['members/create'],
            ['loans'],
            ['loans/create'],
            ['loans/pending'],
            ['loans/ready'],
            ['loans/active'],
            ['loans/overdue'],
            ['loan-extensions'],
            ['returns'],
            ['reports/loans'],
            ['reports/returns'],
            ['reports/overdue'],
            ['reports/statistics'],
            ['users'],
            ['roles'],
            ['permissions'],
            ['audit-logs'],
            ['security-dashboard'],
            ['settings'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_page_renders_for_super_admin(string $uri): void
    {
        $this->seedRoles();
        $this->seedDomainData();

        $response = $this->actingAs($this->makeUser('super-admin'))
            // Beberapa halaman administrasi dilindungi password.confirm (spec §6).
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get("/{$uri}");

        $response->assertOk();
    }

    public function test_detail_pages_render(): void
    {
        $this->seedRoles();
        [$loan, $book] = $this->seedDomainData();

        $admin = $this->makeUser('super-admin');

        $this->actingAs($admin)->get("/catalog/{$book->uuid}")->assertOk();
        $this->actingAs($admin)->get("/loans/{$loan->uuid}")->assertOk();
        $this->actingAs($admin)->get("/books/{$book->uuid}/edit")->assertOk();
    }

    public function test_member_pages_render_for_an_anggota(): void
    {
        $this->seedRoles();

        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user);
        $book = $this->makeBookWithCopy();
        $loan = app(CreateLoan::class)->handle($member, [$book->id]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/loans')->assertOk();
        $this->actingAs($user)->get('/loans/create')->assertOk();
        $this->actingAs($user)->get('/loan-extensions')->assertOk();
        $this->actingAs($user)->get("/loans/{$loan->uuid}")->assertOk();
    }

    /**
     * Satu peminjaman BORROWED + satu pengajuan perpanjangan menunggu, plus
     * satu anggota yang punya akun login — cukup untuk mengisi semua tabel.
     *
     * @return array{0: Loan, 1: Book}
     */
    private function seedDomainData(): array
    {
        $staff = $this->makeUser('petugas');
        $user = $this->makeUser('anggota');
        $member = $this->makeMember($user);
        $book = $this->makeBookWithCopy();

        $loan = app(CreateLoan::class)->handle($member, [$book->id]);
        $loan = app(ApproveLoan::class)->handle($loan, $staff);
        $loan = app(HandoverLoan::class)->handle($loan, $loan->items->first()->bookCopy->barcode, $staff, 'loan-proofs/handover.jpg');

        app(RequestLoanExtension::class)->handle($loan, $user, 3, 'Belum selesai membaca');

        return [$loan->fresh(['items.book', 'items.bookCopy', 'member']), $book];
    }
}
