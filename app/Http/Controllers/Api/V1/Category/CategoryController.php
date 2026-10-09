<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
      /**
     * Listar categorías
     *
     * Catálogo completo ordenado por nombre, sin paginar. El `id` es el valor
     * que se manda como `category_id` al crear un curso.
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }
}