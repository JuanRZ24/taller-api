<?php

namespace App\Http\Controllers\Api\V1\Module;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module\StoreModuleRequest;
use App\Http\Requests\Module\UpdateModuleRequest;
use App\Http\Resources\ModuleResource;
use App\Models\Course;
use App\Models\Module;
use App\Services\Module\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleService $moduleService,
    ) {}

    /**
     * Agregar un módulo
     *
     * Crea un módulo al final del curso. La posición la calcula el servidor.
     */
    public function store(StoreModuleRequest $request, Course $course): JsonResponse
    {
        $module = $this->moduleService->store($course, $request->validated());

        return response()->json([
            'module' => new ModuleResource($module->load('contents')),
        ], 201);
    }

    /**
     * Renombrar un módulo
     */
    public function update(UpdateModuleRequest $request, Module $module): JsonResponse
    {
        $module = $this->moduleService->update($module, $request->validated());

        return response()->json([
            'module' => new ModuleResource($module->load('contents')),
        ]);
    }

    /**
     * Eliminar un módulo
     *
     * Borra el módulo y todos sus contenidos. Es definitivo.
     */
    public function destroy(Module $module): Response
    {
        Gate::authorize('update', $module->course);

        $this->moduleService->destroy($module);

        return response()->noContent();
    }
}