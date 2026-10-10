<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}
    
        /**
     * Registrar una cuenta
     *
     * Crea una cuenta nueva con rol de estudiante y devuelve un token de acceso.
     * El rol no se puede elegir: toda cuenta nace como estudiante.
     */
    public function register (RegisterRequest $request): JsonResponse
    {
        $resultado = $this->authService->register($request->validated());
        
        return response()->json([
            'user' => new UserResource($resultado['user']),
            'token' => $resultado['token'],
        ], 201);
    }
    /**
     * Iniciar sesión
     *
     * Valida las credenciales y devuelve un token. Un correo inexistente y una
     * contraseña incorrecta devuelven el mismo mensaje, para no revelar si una
     * cuenta está registrada.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $resultado = $this->authService->login($request->validated());

        return response()->json([
            'user' => new UserResource($resultado['user']),
            'token' => $resultado['token'],
        ], 200);
    }

    /**
     * Cerrar sesión
     *
     * Invalida el token de acceso del usuario.
     */
    public function logout(Request $request): Response
    {
        $this->authService->logout($request->user());

        return response()->noContent();
    } 
    /**
     * Usuario autenticado
     *
     * Devuelve los datos del usuario a partir del token. No incluye el perfil
     * de instructor, aunque el usuario lo tenga.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

}
