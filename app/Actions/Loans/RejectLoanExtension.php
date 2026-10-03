<?php

namespace App\Actions\Loans;

use App\Enums\ExtensionStatus;
use App\Exceptions\LoanException;
use App\Models\LoanExtension;
use App\Models\User;
use App\Notifications\LoanExtensionRejectedNotification;
use App\Support\Activity;

/**
 * Petugas/admin menolak perpanjangan. Jatuh tempo peminjaman tidak berubah;
 * alasan penolakan wajib diisi dan dikirimkan ke anggota (spec §18/§58).
 */
class RejectLoanExtension
{
    public function handle(LoanExtension $extension, User $actor, string $reason): LoanExtension
    {
        if (! $extension->isPending()) {
            throw new LoanException("Pengajuan perpanjangan berstatus {$extension->status->label()} tidak dapat ditolak lagi.");
        }

        $extension->update([
            'status' => ExtensionStatus::REJECTED,
            'decided_by' => $actor->id,
            'decided_at' => now(),
            'decision_note' => $reason,
        ]);

        $extension = $extension->fresh(['loan.member.user']);

        Activity::log('LOAN_EXTENSION_REJECTED', "Extension request rejected for loan {$extension->loan->code}", $extension, $actor);

        $extension->loan->member->user?->notify(new LoanExtensionRejectedNotification($extension));

        return $extension;
    }
}
