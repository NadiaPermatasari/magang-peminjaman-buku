<?php

namespace App\Actions\Loans;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\Loan;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Cancels a loan while it is still possible to (spec §12 "membatalkan
 * pengajuan jika masih memungkinkan") — before any item has been handed
 * over. Releases any reserved copy back to AVAILABLE.
 */
class CancelLoan
{
    public function handle(Loan $loan, User $actor): Loan
    {
        if (! $loan->status->canTransitionTo(LoanStatus::CANCELLED)) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat dibatalkan.");
        }

        DB::transaction(function () use ($loan) {
            foreach ($loan->items()->with('bookCopy')->get() as $item) {
                if ($item->bookCopy && $item->bookCopy->status === BookCopyStatus::RESERVED) {
                    $item->bookCopy->update(['status' => BookCopyStatus::AVAILABLE]);
                }
                $item->update(['status' => LoanStatus::CANCELLED]);
            }

            $loan->update(['status' => LoanStatus::CANCELLED, 'cancelled_at' => now()]);
        });

        Activity::log('LOAN_CANCELLED', "Loan {$loan->code} cancelled", $loan, $actor);

        return $loan->fresh('items');
    }
}
