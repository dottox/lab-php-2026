<?php

namespace App\Http\Resources\ProfessionalProfile;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'bio' => $this->bio,

            'avg_rating' => $this->avg_rating,

            'reviews_count' => $this->reviews_count,

            'is_verified' => $this->is_verified,

            'created_at' => $this->created_at,

            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
