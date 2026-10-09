<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreInstructorRequest;
use App\Http\Resources\InstructorProfileResource;
use App\Http\Resources\UserResource;
use App\Services\Instructor\InstructorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Instructor\UpdateInstructorPhotoRequest;

class InstructorController extends Controller
{
    public function __construct(
        private readonly InstructorService $instructorService,
    ) {}
    /**
     * Darse de alta como instructor
     *
     * Crea el perfil público y cambia el rol a instructor en una sola
     * operación. Solo un estudiante puede usarlo. El token sigue siendo
     * válido: no hace falta volver a iniciar sesión.
     */
    public function store(StoreInstructorRequest $request): JsonResponse
    {
        $user = $request->user();

        $profile = $this->instructorService->store($user, $request->validated());

        return response()->json([
            'user' => new UserResource($user),
            'profile' => new InstructorProfileResource($profile),
        ], 201);
    }

    /**
     * Subir foto de perfil
     *
     * Sube o reemplaza la foto del instructor autenticado. La petición es
     * multipart: no establezcas el `Content-Type` a mano. La foto anterior se
     * borra del servidor.
     */
        public function updatePhoto(UpdateInstructorPhotoRequest $request): JsonResponse
    {
        $profile = $this->instructorService->updatePhoto(
            $request->user(),
            $request->file('photo'),
        );

        return response()->json([
            'profile' => new InstructorProfileResource($profile),
        ]);
    }
}