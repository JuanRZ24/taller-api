<?php

namespace App\Services\Content;

use App\Models\Content;
use App\Models\Module;

class ContentService
{
    public function store(Module $module, array $data): Content
    {
        $content = new Content($this->normalizar($data));
        $content->position = ($module->contents()->max('position') ?? 0) + 1;

        $module->contents()->save($content);

        return $content;
    }

    public function update(Content $content, array $data): Content
    {
        $content->fill($this->normalizar($data));
        $content->save();

        return $content;
    }

    public function destroy(Content $content): void
    {
        $content->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizar(array $data): array
    {
        return [
            'title' => $data['title'],
            'type' => $data['type'],
            'body' => $data['body'] ?? null,
            'url' => $data['url'] ?? null,
        ];
    }
}