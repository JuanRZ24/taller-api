<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'level' => $this->level,
            'cover_url' => $this->cover_path
                ? Storage::disk('public')->url($this->cover_path)
                : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'instructor' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
        ];
    }
}