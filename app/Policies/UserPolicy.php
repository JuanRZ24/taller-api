<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Un administrador no se puede desactivar a si mismo: si fuera el unico,
     * nadie quedaria para reactivarlo.
     */
    public function changeStatus(User $user, User $objetivo): bool
    {
        return $user->role === UserRole::Admin
            && $user->id !== $objetivo->id;
    }
}