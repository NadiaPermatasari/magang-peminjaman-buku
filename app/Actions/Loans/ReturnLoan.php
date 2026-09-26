<?php

namespace App\Actions\Loans;

use App\Actions\Fines\CalculateFine;
use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReturnedNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Petugas scans a barcode to process a return (spec §16/§61): computes the
 * final fine (if late), releases/updates the copy's status based on
 * returned condition, and closes the loan once every item is back.
 */
class ReturnLoan
{
    public function handle(string $barcode, BookCondition $condition, User $actor, ?string $notes = null): array
    {
        return DB::transaction(function () use ($barcode, $condition, $actor, $notes) {
            $copy = BookCopy::query()->where('barcode', $barcode)->lockForUpdate()->first();

            if (! $copy) {
                throw new LoanException('Barcode tidak ditemukan.');
            }

            $item = $copy->loanItems()->whereIn('status', [LoanStatus::BORROWED, LoanStatus::OVERDUE])->first();

            if (! $item) {
                throw new LoanException('Eksemplar ini tidak sedang dalam status dipinjam.');
            }

            $loan = $item->loan;

            $item->update([
                'status' => LoanStatus::RETURNED,
                'returned_at' => now(),
                'condition_on_return' => $condition,
                'notes' => $notes,
            ]);

            $copy->update(['status' => $this->copyStatusFor($condition)]);

            $fine = app(CalculateFine::class)->handle($item->fresh());

            $remaining = $loan->items()->where('status', '!=', LoanStatus::RETURNED)->count();
            $loanClosed = false;

            if ($remaining === 0) {
                $loan->update(['status' => LoanStatus::RETURNED, 'returned_at' => now()]);
                $loanClosed = true;
            }

            Activity::log('LOAN_RETURNED', "Copy {$barcode} returned for loan {$loan->code}", $loan, $actor);

            if ($loanClosed) {
                $loan->member->user?->notify(new LoanReturnedNotification($loan));
            }

            return ['loan' => $loan->fresh(['items.book', 'items.bookCopy']), 'item' => $item->fresh(), 'fine' => $fine instanceof Fine ? $fine : null];
        });
    }

    private function copyStatusFor(BookCondition $condition): BookCopyStatus
    {
        return match ($condition) {
            BookCondition::GOOD => BookCopyStatus::AVAILABLE,
            BookCondition::MINOR_DAMAGE => BookCopyStatus::MAINTENANCE,
            BookCondition::MAJOR_DAMAGE => BookCopyStatus::DAMAGED,
            BookCondition::LOST => BookCopyStatus::LOST,
        };
    }
}
