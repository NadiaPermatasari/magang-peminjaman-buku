<?php

namespace App\Policies;

use App\Models\Fine;
use App\Models\User;

class FinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fines.view') || $user->can('fines.view-own');
    }

    /** IDOR guard (spec §26): a member may only view their own fines. */
    public function view(User $user, Fine $fine): bool
    {
        if ($user->can('fines.view')) {
            return true;
        }

        return $fine->member->user_id === $user->id;
    }

    public function markPaid(User $user, Fine $fine): bool
    {
        return $user->can('fines.mark-paid');
    }

    public function waive(User $user, Fine $fine): bool
    {
        return $user->can('fines.waive');
    }
}
