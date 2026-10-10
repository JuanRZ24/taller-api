<?php

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StoreContentRequest;
use App\Http\Requests\Content\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Models\Module;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ContentController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
    ) {}

    /**
     * Agregar un contenido
     *
     * Crea un contenido al final del módulo. Según el tipo, se exige `body`
     * (texto) o `url` (enlace y video). La posición la calcula el servidor.
     */
    public function store(StoreContentRequest $request, Module $module): JsonResponse
    {
        $content = $this->contentService->store($module, $request->validated());

        return response()->json([
            'content' => new ContentResource($content),
        ], 201);
    }

    /**
     * Modificar un contenido
     *
     * Reemplaza el contenido completo. Si cambia el tipo, los campos que ya no
     * aplican se limpian.
     */
    public function update(UpdateContentRequest $request, Content $content): JsonResponse
    {
        $content = $this->contentService->update($content, $request->validated());

        return response()->json([
            'content' => new ContentResource($content),
        ]);
    }

    /**
     * Eliminar un contenido
     */
    public function destroy(Content $content): Response
    {
        Gate::authorize('update', $content->module->course);

        $this->contentService->destroy($content);

        return response()->noContent();
    }
}