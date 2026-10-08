<?php

namespace App\Http\Requests\Instructor;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Instructor;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=200,min_height=200',
            ],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Solo un instructor puede cambiar su foto de perfil.');
    }
}