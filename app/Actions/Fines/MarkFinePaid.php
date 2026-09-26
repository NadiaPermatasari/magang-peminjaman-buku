<?php

namespace App\Actions\Fines;

use App\Enums\FineStatus;
use App\Exceptions\LoanException;
use App\Models\Fine;
use App\Models\User;
use App\Notifications\FinePaidNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

class MarkFinePaid
{
    public function handle(Fine $fine, User $actor, ?string $method = null, ?string $notes = null): Fine
    {
        if ($fine->status !== FineStatus::UNPAID) {
            throw new LoanException("Denda berstatus {$fine->status->label()} tidak dapat ditandai lunas.");
        }

        DB::transaction(function () use ($fine, $actor, $method, $notes) {
            $fine->payments()->create([
                'amount' => $fine->amount,
                'paid_at' => now(),
                'received_by' => $actor->id,
                'method' => $method,
                'notes' => $notes,
            ]);

            $fine->update(['status' => FineStatus::PAID, 'paid_at' => now()]);
        });

        Activity::log('FINE_MARKED_PAID', "Fine of Rp{$fine->amount} marked as paid", $fine, $actor);

        $fine->member->user?->notify(new FinePaidNotification($fine));

        return $fine->fresh();
    }
}
