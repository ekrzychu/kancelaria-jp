<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActiveAdministrator();
    }

    public function create(User $actor): bool
    {
        return $actor->isActiveAdministrator();
    }

    public function update(User $actor, User $account): bool
    {
        return $actor->isActiveAdministrator();
    }

    public function changeStatus(User $actor, User $account): bool
    {
        return $actor->isActiveAdministrator();
    }
}
