<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreInstructorRequest;
use App\Http\Resources\InstructorProfileResource;
use App\Http\Resources\UserResource;
use App\Http\Requests\Instructor\UpdateInstructorRequest;
use Illuminate\Http\Request;
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



    /**
     * Actualizar perfil
     *
     * Actualiza el perfil del instructor autenticado. Solo se pueden actualizar
     * los campos `headline`, `specialty` y `biography`.
     */
    public function update(UpdateInstructorRequest $request): JsonResponse
    {
        $profile = $this -> instructorService->update(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'profile' => new InstructorProfileResource($profile),
        ]);

        }
    /**
     * Ver mi perfil de instructor
     *
     * Devuelve el perfil público del instructor autenticado. Sirve para
     * llenar el formulario de edición con los datos actuales.
     */
        public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->instructorProfile;

        abort_if($profile === null, 404, 'Todavia no tienes un perfil de instructor.');

        return response()->json([
            'profile' => new InstructorProfileResource($profile),
        ]);
    }
}