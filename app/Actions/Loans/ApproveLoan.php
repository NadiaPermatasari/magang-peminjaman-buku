<?php

namespace App\Actions\Loans;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanApprovedNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Petugas approves a pending loan (spec §14). Allocates one AVAILABLE copy
 * per item under a row lock so two concurrent approvals can never reserve
 * the same physical copy (spec §40).
 */
class ApproveLoan
{
    public function handle(Loan $loan, User $actor): Loan
    {
        if (! $loan->status->canTransitionTo(LoanStatus::APPROVED)) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat disetujui.");
        }

        DB::transaction(function () use ($loan, $actor) {
            $loan->loadMissing('items');

            foreach ($loan->items as $item) {
                $copy = BookCopy::query()
                    ->where('book_id', $item->book_id)
                    ->where('status', BookCopyStatus::AVAILABLE)
                    ->lockForUpdate()
                    ->first();

                if (! $copy) {
                    throw new LoanException('Tidak ada eksemplar tersedia untuk salah satu buku pada pengajuan ini. Tolak pengajuan atau tunggu eksemplar tersedia.');
                }

                $copy->update(['status' => BookCopyStatus::RESERVED]);
                $item->update(['book_copy_id' => $copy->id, 'status' => LoanStatus::APPROVED]);
            }

            $loan->update([
                'status' => LoanStatus::APPROVED,
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'pickup_deadline' => now()->addDays((int) setting('pickup_deadline_days', 2)),
            ]);
        });

        Activity::log('LOAN_APPROVED', "Loan {$loan->code} approved", $loan, $actor);

        $loan->member->user?->notify(new LoanApprovedNotification($loan));

        return $loan->fresh(['items.book', 'items.bookCopy']);
    }
}
