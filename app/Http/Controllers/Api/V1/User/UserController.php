<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * Desactivar una cuenta
     *
     * Bloquea el acceso del usuario y revoca todos sus tokens, asi que
     * cualquier sesion abierta deja de servir de inmediato. Un administrador
     * no se puede desactivar a si mismo.
     */
    public function deactivate(User $user): JsonResponse
    {
        Gate::authorize('changeStatus', $user);

        return response()->json([
            'user' => new UserResource($this->userService->deactivate($user)),
        ]);
    }

    /**
     * Reactivar una cuenta
     *
     * Devuelve el acceso al usuario. Tendra que volver a iniciar sesion
     * porque sus tokens se revocaron al desactivarlo.
     */
    public function activate(User $user): JsonResponse
    {
        Gate::authorize('changeStatus', $user);

        return response()->json([
            'user' => new UserResource($this->userService->activate($user)),
        ]);
    }
}