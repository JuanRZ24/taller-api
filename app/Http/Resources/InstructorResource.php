<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * Quien imparte el curso. Envuelve un User, no un InstructorProfile:
     * el nombre vive en el usuario y el resto del perfil en su relacion.
     * 
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'profile' => $this->whenLoaded(
                'instructorProfile',
                fn () => $this->instructorProfile
                    ? new InstructorProfileResource($this->instructorProfile)
                    : null,
            ),
        ];
    }
}