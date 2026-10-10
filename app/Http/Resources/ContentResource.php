<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ContentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'position' => $this->position,
            'body' => $this->body,
            'url' => $this->url,
            'file_url' => $this->file_path
                ? Storage::disk('public')->url($this->file_path)
                : null,
        ];
    }
}