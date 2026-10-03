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
    

    public function register (RegisterRequest $request): JsonResponse
    {
        $resultado = $this->authService->register($request->validated());
        
        return response()->json([
            'user' => new UserResource($resultado['user']),
            'token' => $resultado['token'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $resultado = $this->authService->login($request->validated());

        return response()->json([
            'user' => new UserResource($resultado['user']),
            'token' => $resultado['token'],
        ], 200);
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout($request->user());

        return response()->noContent();
    } 

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

}
