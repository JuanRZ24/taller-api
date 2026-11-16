<?php

namespace App\Services\User;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return User::query()->orderBy('name')->paginate($perPage);
    }

    /**
     * Desactivar revoca todos los tokens del usuario. Sin eso, quien ya
     * tuviera sesion abierta seguiria usando la API hasta cerrarla.
     */
    public function deactivate(User $user): User
    {
        $user->status = UserStatus::Inactive;
        $user->save();

        $user->tokens()->delete();

        return $user;
    }

    public function activate(User $user): User
    {
        $user->status = UserStatus::Active;
        $user->save();

        return $user;
    }
}