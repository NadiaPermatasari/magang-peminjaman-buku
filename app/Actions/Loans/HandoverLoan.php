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
 * Petugas menyerahkan satu eksemplar yang sudah direservasi ke anggota
 * (spec §15/§61), dengan bukti foto serah terima bila diunggah. Satu
 * pemanggilan per eksemplar — begitu item terakhir diserahkan, peminjaman
 * ikut berubah menjadi BORROWED.
 */
class HandoverLoan
{
    /**
     * @param  string|null  $photoPath  Path bukti foto pada disk `public`.
     */
    public function handle(Loan $loan, string $barcode, User $actor, ?string $photoPath = null, ?string $conditionOnBorrow = null): Loan
    {
        if ($loan->status !== LoanStatus::APPROVED) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat diserahkan.");
        }

        DB::transaction(function () use ($loan, $barcode, $photoPath, $conditionOnBorrow) {
            $copy = BookCopy::query()->where('barcode', $barcode)->lockForUpdate()->first();

            if (! $copy) {
                throw new LoanException('Eksemplar tidak ditemukan.');
            }

            $item = $loan->items()->where('book_copy_id', $copy->id)->where('status', LoanStatus::APPROVED)->first();

            if (! $item) {
                throw new LoanException('Eksemplar ini bukan eksemplar yang direservasi untuk pengajuan ini.');
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
                'handover_photo_path' => $photoPath ?? $item->handover_photo_path,
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
