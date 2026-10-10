<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderContentsRequest extends FormRequest
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
        $module = $this->route('module');

        return [
            'contents' => ['required', 'array', 'size:'.$module->contents()->count()],
            'contents.*' => [
                'required',
                'integer',
                'distinct',
                Rule::in($module->contents()->pluck('id')->all()),
            ],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'No puedes modificar este curso.');
    }
}