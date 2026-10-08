<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;

class StoreInstructorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Student;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'headline' => ['required', 'string', 'max:150'],
            'specialty' => ['required', 'string', 'max:100'],
            'biography' => ['required', 'string', 'min:50', 'max:2000'],
        ];
    }

        protected function failedAuthorization(): void
    {
        abort(403, 'Solo un estudiante puede darse de alta como instructor.');
    }
}
