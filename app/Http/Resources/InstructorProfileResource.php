<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InstructorProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'headline' => $this->headline,
            'specialty' => $this->specialty,
            'biography' => $this->biography,
            'photo_url' => $this->photo_path
                ? Storage::disk('public')->url($this->photo_path)
                : null,
        ];
    }
}