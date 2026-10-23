<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ModuleOutlineResource extends ModuleResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'contents' => ContentOutlineResource::collection($this->whenLoaded('contents')),
        ];
    }
}