<?php

namespace App\Http\Resources\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'professional_id' => $this->professional_id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'modality' => $this->modality,
            'address' => $this->address,
            'link' => $this->link,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'max_bookings_per_client' => $this->max_bookings_per_client,
            'min_reschedule_minutes' => $this->min_reschedule_minutes,
            'buffer_minutes' => $this->buffer_minutes,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
