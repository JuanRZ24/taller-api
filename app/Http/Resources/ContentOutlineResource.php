<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentOutlineResource extends JsonResource
{
    /**
     * Version publica de un contenido: el esqueleto del temario, sin el
     * material. Quien no es dueno del curso ve que existe la leccion y de
     * que tipo es, pero no su texto ni su enlace.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'position' => $this->position,
        ];
    }
}