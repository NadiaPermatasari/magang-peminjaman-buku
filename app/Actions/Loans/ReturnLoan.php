<?php

namespace App\Actions\Loans;

use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Notifications\LoanReturnedNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Petugas memproses pengembalian satu eksemplar (spec §16): eksemplar
 * dipilih dari daftar peminjaman aktif — tidak lagi lewat scan barcode —
 * dan wajib disertai bukti foto buku yang dikembalikan. Status eksemplar
 * mengikuti kondisi yang dilaporkan, dan peminjaman ditutup begitu seluruh
 * itemnya kembali.
 */
class ReturnLoan
{
    /**
     * @param  string|null  $photoPath  Path bukti foto pada disk `public`.
     * @return array{loan: Loan, item: LoanItem}
     */
    public function handle(LoanItem $item, BookCondition $condition, User $actor, ?string $photoPath = null, ?string $notes = null): array
    {
        return DB::transaction(function () use ($item, $condition, $actor, $photoPath, $notes) {
            // Baca ulang di dalam transaksi + row lock supaya dua petugas
            // tidak bisa memproses pengembalian item yang sama bersamaan.
            $item = LoanItem::whereKey($item->getKey())->lockForUpdate()->first();

            if (! $item) {
                throw new LoanException('Data item peminjaman tidak ditemukan.');
            }

            if (! in_array($item->status, [LoanStatus::BORROWED, LoanStatus::OVERDUE], true)) {
                throw new LoanException('Eksemplar ini tidak sedang dalam status dipinjam.');
            }

            $loan = $item->loan;
            $copy = $item->bookCopy;

            $item->update([
                'status' => LoanStatus::RETURNED,
                'returned_at' => now(),
                'condition_on_return' => $condition,
                'return_photo_path' => $photoPath ?? $item->return_photo_path,
                'notes' => $notes,
            ]);

            $copy?->update(['status' => $this->copyStatusFor($condition)]);

            $remaining = $loan->items()->where('status', '!=', LoanStatus::RETURNED)->count();
            $loanClosed = false;

            if ($remaining === 0) {
                $loan->update(['status' => LoanStatus::RETURNED, 'returned_at' => now()]);
                $loanClosed = true;
            }

            $label = $copy?->barcode ?? $item->book->title;
            Activity::log('LOAN_RETURNED', "Copy {$label} returned for loan {$loan->code}", $loan, $actor);

            if ($loanClosed) {
                $loan->member->user?->notify(new LoanReturnedNotification($loan));
            }

            return ['loan' => $loan->fresh(['items.book', 'items.bookCopy']), 'item' => $item->fresh()];
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
