<?php

namespace App\Http\Requests\Content;

use App\Enums\ContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('module')->course);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(ContentType::class)->only([
                ContentType::Text,
                ContentType::Link,
                ContentType::Video,
            ])],
            'body' => ['required_if:type,text', 'prohibited_unless:type,text', 'nullable', 'string'],
            'url' => ['required_if:type,link,video', 'prohibited_unless:type,link,video', 'nullable', 'url', 'max:500'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'No puedes modificar este curso.');
    }
}