<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function update(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPic() && $complaint->room && $complaint->room->pic_id === $user->id;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $user->isAdmin() || ($user->isPic() && $complaint->room && $complaint->room->pic_id === $user->id) || $complaint->reporter_id === $user->id;
    }
}
