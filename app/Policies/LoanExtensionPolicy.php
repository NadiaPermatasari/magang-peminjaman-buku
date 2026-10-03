<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\LoanExtension;
use App\Models\User;

class LoanExtensionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('loan-extensions.view') || $user->can('loan-extensions.create');
    }

    /**
     * IDOR guard (spec §26): anggota hanya boleh melihat pengajuan atas
     * peminjamannya sendiri, petugas dengan loan-extensions.view melihat semua.
     */
    public function view(User $user, LoanExtension $extension): bool
    {
        if ($user->can('loan-extensions.view')) {
            return true;
        }

        return $extension->loan->member->user_id === $user->id;
    }

    /** Hanya anggota pemilik peminjaman yang boleh mengajukan perpanjangan. */
    public function create(User $user, Loan $loan): bool
    {
        return $user->can('loan-extensions.create')
            && $loan->member->user_id === $user->id
            && $loan->isActive();
    }

    public function decide(User $user, LoanExtension $extension): bool
    {
        return $user->can('loan-extensions.approve') && $extension->isPending();
    }
}
