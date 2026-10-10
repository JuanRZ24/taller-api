<?php

namespace App\Http\Requests\Course;

use App\Enums\CourseLevel;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Course::class);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:100', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'level' => ['required', Rule::enum(CourseLevel::class)],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Solo un instructor puede crear cursos.');
    }
}