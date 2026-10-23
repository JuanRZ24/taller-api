<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\Course\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseOutlineResource;
use App\Models\Course;
use App\Http\Requests\Course\IndexCourseRequest;
use App\Http\Resources\CourseCatalogResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;


class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courseService,
    ) {}

    /**
     * Crear un curso
     *
     * Crea un curso en estado borrador a nombre del instructor autenticado.
     * El estado no se puede elegir: todo curso nace como borrador.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->store(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'course' => new CourseResource($course->load('category')),
        ], 201);
    }

    /**
     * Mis cursos
     *
     * Lista paginada de los cursos del instructor autenticado, del más
     * reciente al más antiguo.
     */
    public function index(Request $request): JsonResponse
    {
        $courses = $this->courseService->paginateByInstructor(
            $request->user()
        );

        return response()->json([
            'courses' => CourseResource::collection($courses->items()),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
        ]);
    }

    /**
     * Ver un curso
     *
     * Devuelve un curso. Accesible para su dueño en cualquier estado, y para
     * cualquiera si está publicado.
     *
     * @tokenOpcional
     */
        public function show(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('view', $course);

        $course->load('category', 'modules.contents');

        return response()->json([
            'course' => $request->user()?->id === $course->user_id
                ? new CourseResource($course)
                : new CourseOutlineResource($course),
        ]);
    }

    /**
     * Modificar un curso
     *
     * Actualización parcial: solo se modifican los campos enviados. Únicamente
     * el dueño puede modificarlo, y solo si está en borrador o rechazado.
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $course = $this->courseService->update($course, $request->validated());

        return response()->json([
            'course' => new CourseResource($course->load('category')),
        ]);
    }

    /**
     * Eliminar un curso
     *
     * Borra el curso junto con sus módulos, contenidos y su imagen de portada.
     * Únicamente el dueño, y solo si está en borrador o rechazado.
     */
    public function destroy(Course $course): Response
    {
        Gate::authorize('delete', $course);

        $this->courseService->destroy($course);

        return response()->noContent();
    }


        /**
     * Catálogo de cursos
     *
     * Lista paginada de los cursos publicados. Ruta pública, sin token.
     * Acepta filtros por categoría, nivel y búsqueda en el título.
     */
    public function catalog(IndexCourseRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $courses = $this->courseService->paginatePublished(
            $filters,
            $filters['per_page'] ?? 12,
        );

        return response()->json([
            'courses' => CourseCatalogResource::collection($courses->items()),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
        ]);
    }
}