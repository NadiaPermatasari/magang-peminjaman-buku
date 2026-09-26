<?php

namespace App\Actions\Fines;

use App\Enums\FineStatus;
use App\Models\Fine;
use App\Models\LoanItem;
use App\Notifications\FineCreatedNotification;
use App\Support\Activity;

/**
 * Computes and records the fine for a returned loan item (spec §16 step 5,
 * §18). Config is read from settings, never hardcoded. No-op (returns null)
 * when fines are disabled or the item was not actually late.
 */
class CalculateFine
{
    public function handle(LoanItem $item): ?Fine
    {
        if (! setting('fine_enabled', true) || ! $item->due_at) {
            return null;
        }

        $lateDays = $this->lateDays($item);
        $gracePeriod = (int) setting('fine_grace_period', 0);

        if ($lateDays <= $gracePeriod) {
            return null;
        }

        $billableDays = $lateDays - $gracePeriod;
        $rate = (int) setting('fine_amount_per_day', 0);
        $amount = $billableDays * $rate;

        $maximumFine = setting('maximum_fine');
        if ($maximumFine !== null && $maximumFine !== '') {
            $amount = min($amount, (int) $maximumFine);
        }

        if ($amount <= 0) {
            return null;
        }

        $fine = Fine::create([
            'loan_item_id' => $item->id,
            'member_id' => $item->loan->member_id,
            'type' => 'LATE_RETURN',
            'late_days' => $lateDays,
            'rate' => $rate,
            'amount' => $amount,
            'status' => FineStatus::UNPAID,
            'calculated_at' => now(),
        ]);

        Activity::log('FINE_CREATED', "Fine of Rp{$amount} for {$billableDays} late day(s)", $fine);

        $item->loan->member->user?->notify(new FineCreatedNotification($fine));

        return $fine;
    }

    private function lateDays(LoanItem $item): int
    {
        $returnedAt = $item->returned_at ?? now();

        return $returnedAt->greaterThan($item->due_at)
            ? (int) $item->due_at->diffInDays($returnedAt)
            : 0;
    }
}
