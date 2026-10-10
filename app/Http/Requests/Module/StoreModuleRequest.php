<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('course'));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'No puedes modificar este curso.');
    }
}