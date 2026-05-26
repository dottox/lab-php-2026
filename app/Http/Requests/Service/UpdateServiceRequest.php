<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'nullable', 'uuid', 'exists:companies,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'duration_minutes' => ['sometimes', 'required', 'integer', Rule::in([15, 30, 45, 60, 90, 120])],
            'modality' => ['sometimes', 'required', Rule::in(['presencial', 'remota', 'hibrida'])],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'link' => ['sometimes', 'nullable', 'url', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'max_bookings_per_client' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'min_reschedule_minutes' => ['sometimes', 'required', 'integer', 'min:0'],
            'buffer_minutes' => ['sometimes', 'required', 'integer', 'min:0'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
