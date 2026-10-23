<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Instructor;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
   public function rules(): array
    {
        return [
            'headline' => ['sometimes', 'required', 'string', 'max:150'],
            'specialty' => ['sometimes', 'required', 'string', 'max:100'],
            'biography' => ['sometimes', 'required', 'string', 'min:50', 'max:2000'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Solo un instructor puede editar su perfil.');
    }

}
