<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CourseOutlineResource extends CourseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'modules' => ModuleOutlineResource::collection($this->whenLoaded('modules')),
        ];
    }
}