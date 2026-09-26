<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('members.view');
    }

    /**
     * IDOR guard (spec §26): staff with members.view can see any member;
     * an anggota may only see their own linked member record.
     */
    public function view(User $user, Member $member): bool
    {
        if ($user->can('members.view')) {
            return true;
        }

        return $member->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('members.create');
    }

    public function update(User $user, Member $member): bool
    {
        return $user->can('members.update');
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->can('members.delete');
    }
}
