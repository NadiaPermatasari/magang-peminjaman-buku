<?php

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanRejectedNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

class RejectLoan
{
    public function handle(Loan $loan, User $actor, string $reason): Loan
    {
        if (! $loan->status->canTransitionTo(LoanStatus::REJECTED)) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat ditolak.");
        }

        DB::transaction(function () use ($loan, $actor, $reason) {
            $loan->items()->update(['status' => LoanStatus::REJECTED]);

            $loan->update([
                'status' => LoanStatus::REJECTED,
                'rejected_by' => $actor->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);
        });

        Activity::log('LOAN_REJECTED', "Loan {$loan->code} rejected: {$reason}", $loan, $actor);

        $loan->member->user?->notify(new LoanRejectedNotification($loan));

        return $loan->fresh('items');
    }
}
