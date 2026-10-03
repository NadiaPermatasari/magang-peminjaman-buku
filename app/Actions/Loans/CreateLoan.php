<?php

namespace App\Actions\Loans;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\User;
use App\Notifications\LoanSubmittedNotification;
use App\Notifications\NewLoanPendingNotification;
use App\Support\Activity;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Anggota submits a loan request (spec §12/§14). Only validates eligibility
 * and creates PENDING records — no book copy is allocated yet, that happens
 * under a row lock at approval time (ApproveLoan) to keep this transaction
 * short and avoid holding locks across the whole pending period.
 */
class CreateLoan
{
    /**
     * @param  list<int>  $bookIds
     */
    public function handle(Member $member, array $bookIds, ?string $notes = null): Loan
    {
        $bookIds = array_values(array_unique($bookIds));

        if (empty($bookIds)) {
            throw new LoanException('Pilih minimal satu buku untuk diajukan.');
        }

        $this->assertMemberEligible($member, count($bookIds));

        $books = Book::whereIn('id', $bookIds)->get()->keyBy('id');

        foreach ($bookIds as $bookId) {
            $book = $books->get($bookId);

            if (! $book || ! $book->is_active) {
                throw new LoanException('Salah satu buku yang dipilih tidak tersedia.');
            }

            if ($book->copies()->where('status', BookCopyStatus::AVAILABLE)->count() < 1) {
                throw new LoanException("Tidak ada eksemplar tersedia untuk buku \"{$book->title}\" saat ini.");
            }
        }

        $loan = DB::transaction(function () use ($member, $bookIds, $notes) {
            $loan = Loan::create([
                'code' => $this->nextCode(),
                'member_id' => $member->id,
                'status' => LoanStatus::PENDING,
                'requested_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($bookIds as $bookId) {
                $loan->items()->create([
                    'book_id' => $bookId,
                    'status' => LoanStatus::PENDING,
                ]);
            }

            return $loan;
        });

        Activity::log('LOAN_CREATED', "Loan {$loan->code} submitted by {$member->name}", $loan);

        $member->user?->notify(new LoanSubmittedNotification($loan));

        User::permission('loans.approve')->get()->each(
            fn (User $staff) => $staff->notify(new NewLoanPendingNotification($loan))
        );

        return $loan->load('items.book');
    }

    private function assertMemberEligible(Member $member, int $requestedCount): void
    {
        if (! $member->isActive()) {
            throw new LoanException('Anggota tidak aktif atau masa keanggotaan telah berakhir.');
        }

        $activeStatuses = [LoanStatus::PENDING, LoanStatus::APPROVED, LoanStatus::BORROWED, LoanStatus::OVERDUE];

        $activeItemCount = LoanItem::whereHas(
            'loan',
            fn ($q) => $q->where('member_id', $member->id)->whereIn('status', $activeStatuses)
        )->whereIn('status', $activeStatuses)->count();

        $maxActiveLoans = (int) setting('max_active_loans', 3);

        if ($activeItemCount + $requestedCount > $maxActiveLoans) {
            throw new LoanException("Batas maksimum peminjaman aktif ({$maxActiveLoans} buku) akan terlampaui.");
        }

        if (setting('block_if_overdue', true)) {
            $hasOverdue = Loan::where('member_id', $member->id)->where('status', LoanStatus::OVERDUE)->exists();

            if ($hasOverdue) {
                throw new LoanException('Anggota memiliki peminjaman yang terlambat dan belum dikembalikan.');
            }
        }
    }

    /**
     * Human-readable, concurrency-safe loan code: PJ-YYYYMMDD-000001 (spec
     * §13). Driver-portable (works on MySQL in dev/prod and SQLite in
     * tests): lockForUpdate() the counter row for today inside a
     * transaction, or insert it if this is the first loan of the day. The
     * rare race on two simultaneous "first loan of the day" transactions is
     * resolved by retrying as an update if the insert hits the unique key.
     */
    private function nextCode(): string
    {
        $today = now()->toDateString();

        $number = DB::transaction(function () use ($today) {
            $row = DB::table('loan_number_sequences')->where('date', $today)->lockForUpdate()->first();

            if ($row) {
                $next = $row->last_number + 1;
                DB::table('loan_number_sequences')->where('date', $today)->update(['last_number' => $next]);

                return $next;
            }

            try {
                DB::table('loan_number_sequences')->insert(['date' => $today, 'last_number' => 1]);

                return 1;
            } catch (QueryException) {
                $row = DB::table('loan_number_sequences')->where('date', $today)->lockForUpdate()->first();
                $next = $row->last_number + 1;
                DB::table('loan_number_sequences')->where('date', $today)->update(['last_number' => $next]);

                return $next;
            }
        });

        return 'PJ-'.now()->format('Ymd').'-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}
