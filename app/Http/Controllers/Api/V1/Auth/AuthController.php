<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

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
}
