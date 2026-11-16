<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class RejectCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('moderate', $this->route('course'));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:20', 'max:500'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Solo un administrador puede moderar un curso en revision.');
    }
}