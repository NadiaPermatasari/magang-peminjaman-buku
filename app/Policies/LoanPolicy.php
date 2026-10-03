<?php

namespace App\Policies;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('loans.view-all') || $user->can('loans.view-own');
    }

    /**
     * IDOR guard (spec §26): a member may only view their own loans unless
     * they hold loans.view-all.
     */
    public function view(User $user, Loan $loan): bool
    {
        if ($user->can('loans.view-all')) {
            return true;
        }

        return $loan->member->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('loans.create');
    }

    public function approve(User $user, Loan $loan): bool
    {
        return $user->can('loans.approve') && $loan->status === LoanStatus::PENDING;
    }

    public function reject(User $user, Loan $loan): bool
    {
        return $user->can('loans.reject') && $loan->status === LoanStatus::PENDING;
    }

    public function handover(User $user, Loan $loan): bool
    {
        return $user->can('loans.handover') && $loan->status === LoanStatus::APPROVED;
    }

    /**
     * Bukti foto serah terima boleh diunggah/diperbarui petugas selama
     * peminjaman belum selesai — termasuk dari halaman Peminjaman Aktif,
     * untuk item yang tadinya diserahkan tanpa foto.
     */
    public function uploadHandoverPhoto(User $user, Loan $loan): bool
    {
        return ($user->can('loans.handover') || $user->can('returns.process'))
            && in_array($loan->status, [LoanStatus::APPROVED, LoanStatus::BORROWED, LoanStatus::OVERDUE], true);
    }

    public function cancel(User $user, Loan $loan): bool
    {
        if (! in_array($loan->status, [LoanStatus::PENDING, LoanStatus::APPROVED], true)) {
            return false;
        }

        if ($user->can('loans.cancel') && $loan->member->user_id === $user->id) {
            return true;
        }

        return $user->can('loans.approve');
    }
}
