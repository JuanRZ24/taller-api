<?php

namespace App\Http\Controllers\Api\V1\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Enrollment\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
    ) {}

    /**
     * Inscribirse a un curso
     *
     * Inscribe al usuario autenticado en un curso publicado. No se puede
     * uno inscribir a su propio curso ni dos veces al mismo.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('enroll', $course);

        $user = $request->user();

        abort_if(
            $this->enrollmentService->isEnrolled($user, $course),
            409,
            'Ya estas inscrito en este curso.',
        );

        $this->enrollmentService->store($user, $course);

        return response()->json([
            'message' => 'Inscripcion realizada.',
        ], 201);
    }

    /**
     * Darse de baja de un curso
     *
     * Elimina la inscripcion del usuario autenticado. Si no estaba
     * inscrito, responde igual: el resultado final es el mismo.
     */
    public function destroy(Request $request, Course $course): Response
    {
        $this->enrollmentService->destroy($request->user(), $course);

        return response()->noContent();
    }
}