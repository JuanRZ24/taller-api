<?php

namespace App\Http\Requests\Course;

use App\Enums\CourseLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'level' => ['sometimes', Rule::enum(CourseLevel::class)],
            'search' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}