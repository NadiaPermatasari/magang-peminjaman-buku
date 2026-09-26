<?php

namespace App\Actions\Loans;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Petugas scans a barcode to hand a reserved copy over to the member (spec
 * §15/§61). One call per scanned copy — when the last remaining item of the
 * loan is handed over, the loan itself flips to BORROWED.
 */
class HandoverLoan
{
    public function handle(Loan $loan, string $barcode, User $actor, ?string $conditionOnBorrow = null): Loan
    {
        if ($loan->status !== LoanStatus::APPROVED) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat diserahkan.");
        }

        DB::transaction(function () use ($loan, $barcode, $conditionOnBorrow) {
            $copy = BookCopy::query()->where('barcode', $barcode)->lockForUpdate()->first();

            if (! $copy) {
                throw new LoanException('Barcode tidak ditemukan.');
            }

            $item = $loan->items()->where('book_copy_id', $copy->id)->where('status', LoanStatus::APPROVED)->first();

            if (! $item) {
                throw new LoanException('Barcode ini bukan eksemplar yang direservasi untuk pengajuan ini.');
            }

            if (! $copy->status->canTransitionTo(BookCopyStatus::BORROWED)) {
                throw new LoanException("Eksemplar berstatus {$copy->status->label()} tidak dapat diserahkan.");
            }

            $dueAt = now()->addDays((int) setting('loan_duration_days', 7));

            $copy->update(['status' => BookCopyStatus::BORROWED]);
            $item->update([
                'status' => LoanStatus::BORROWED,
                'borrowed_at' => now(),
                'due_at' => $dueAt,
                'condition_on_borrow' => $conditionOnBorrow ?? $copy->condition->value,
            ]);

            $remaining = $loan->items()->where('status', '!=', LoanStatus::BORROWED)->count();

            if ($remaining === 0) {
                $loan->update([
                    'status' => LoanStatus::BORROWED,
                    'borrowed_at' => now(),
                    'due_at' => $loan->items()->max('due_at'),
                ]);
            }
        });

        Activity::log('LOAN_HANDED_OVER', "Copy {$barcode} handed over for loan {$loan->code}", $loan, $actor);

        return $loan->fresh(['items.book', 'items.bookCopy']);
    }
}
