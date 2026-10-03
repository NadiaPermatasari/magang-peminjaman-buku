<?php

namespace App\Actions\Loans;

use App\Enums\ExtensionStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Models\LoanExtension;
use App\Models\User;
use App\Notifications\LoanExtensionApprovedNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Petugas/admin menyetujui perpanjangan: jatuh tempo peminjaman dan seluruh
 * item yang belum kembali digeser sebanyak hari yang disetujui. Untuk
 * peminjaman yang sudah telat, perhitungan dimulai dari hari ini (bukan dari
 * jatuh tempo lama yang sudah lewat) dan statusnya kembali ke BORROWED.
 */
class ApproveLoanExtension
{
    public function handle(LoanExtension $extension, User $actor, ?string $note = null): LoanExtension
    {
        if (! $extension->isPending()) {
            throw new LoanException("Pengajuan perpanjangan berstatus {$extension->status->label()} tidak dapat disetujui lagi.");
        }

        $loan = $extension->loan;

        if (! $loan->isActive()) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat diperpanjang.");
        }

        DB::transaction(function () use ($extension, $loan, $actor, $note) {
            $base = $loan->due_at && $loan->due_at->isFuture() ? $loan->due_at->copy() : now();
            $newDueAt = $base->addDays($extension->days);

            $loan->items()
                ->whereIn('status', [LoanStatus::BORROWED, LoanStatus::OVERDUE])
                ->update(['due_at' => $newDueAt]);

            $loan->update([
                'due_at' => $newDueAt,
                'status' => LoanStatus::BORROWED,
            ]);

            $loan->items()
                ->where('status', LoanStatus::OVERDUE)
                ->update(['status' => LoanStatus::BORROWED]);

            $extension->update([
                'status' => ExtensionStatus::APPROVED,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_note' => $note,
                'new_due_at' => $newDueAt,
            ]);
        });

        $extension = $extension->fresh(['loan.member.user']);

        Activity::log(
            'LOAN_EXTENSION_APPROVED',
            "Extension of {$extension->days} day(s) approved for loan {$loan->code}",
            $extension,
            $actor,
            values: ['before' => ['due_at' => (string) $extension->previous_due_at], 'after' => ['due_at' => (string) $extension->new_due_at]],
        );

        $extension->loan->member->user?->notify(new LoanExtensionApprovedNotification($extension));

        return $extension;
    }
}
