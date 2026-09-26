<?php

namespace App\Actions\Loans;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Releases an APPROVED loan whose pickup_deadline has passed without the
 * member collecting the book(s) (spec §17/§20). Used by the
 * ExpireApprovedLoans scheduled command.
 */
class ExpireLoan
{
    public function handle(Loan $loan): Loan
    {
        if ($loan->status !== LoanStatus::APPROVED) {
            return $loan;
        }

        DB::transaction(function () use ($loan) {
            foreach ($loan->items()->with('bookCopy')->get() as $item) {
                if ($item->bookCopy && $item->bookCopy->status === BookCopyStatus::RESERVED) {
                    $item->bookCopy->update(['status' => BookCopyStatus::AVAILABLE]);
                }
                $item->update(['status' => LoanStatus::EXPIRED]);
            }

            $loan->update(['status' => LoanStatus::EXPIRED, 'expired_at' => now()]);
        });

        Activity::log('LOAN_EXPIRED', "Loan {$loan->code} expired (not picked up in time)", $loan);

        return $loan->fresh('items');
    }
}
