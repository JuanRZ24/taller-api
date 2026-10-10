<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderModulesRequest extends FormRequest
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
        $course = $this->route('course');

        return [
            'modules' => ['required', 'array', 'size:'.$course->modules()->count()],
            'modules.*' => [
                'required',
                'integer',
                'distinct',
                Rule::in($course->modules()->pluck('id')->all()),
            ],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'No puedes modificar este curso.');
    }
}