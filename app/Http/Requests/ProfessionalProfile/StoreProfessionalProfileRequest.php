<?php

namespace App\Http\Requests\ProfessionalProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfessionalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string'],
        ];
    }
}
