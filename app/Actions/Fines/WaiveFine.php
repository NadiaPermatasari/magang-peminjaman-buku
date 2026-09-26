<?php

namespace App\Actions\Fines;

use App\Enums\FineStatus;
use App\Exceptions\LoanException;
use App\Models\Fine;
use App\Models\User;
use App\Support\Activity;

/**
 * Waiving a fine is restricted (spec §18/§58 "fine waiver = restricted") —
 * gated by the separate fines.waive permission, always requires a reason,
 * and records before/after values in the audit log.
 */
class WaiveFine
{
    public function handle(Fine $fine, User $actor, string $reason): Fine
    {
        if ($fine->status !== FineStatus::UNPAID) {
            throw new LoanException("Denda berstatus {$fine->status->label()} tidak dapat dibebaskan.");
        }

        $before = ['status' => $fine->status->value, 'amount' => $fine->amount];

        $fine->update([
            'status' => FineStatus::WAIVED,
            'waived_at' => now(),
            'waived_by' => $actor->id,
            'waive_reason' => $reason,
        ]);

        Activity::log('FINE_WAIVED', "Fine of Rp{$fine->amount} waived: {$reason}", $fine, $actor, values: [
            'before' => $before,
            'after' => ['status' => FineStatus::WAIVED->value, 'reason' => $reason],
        ]);

        return $fine->fresh();
    }
}
