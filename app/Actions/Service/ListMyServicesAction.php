<?php

namespace App\Actions\Service;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ListMyServicesAction
{
    public function __invoke(): Collection
    {
        $user = auth('user_jwt')->user();

        $professionalProfile = $user->professionalProfile;

        if (! $professionalProfile) {
            throw new ApiException(
                error: 'ProfessionalProfileNotFound',
                message: 'Professional profile not found for the authenticated user.',
                status: Response::HTTP_NOT_FOUND
            );
        }

        return $professionalProfile
            ->services()
            ->latest()
            ->get();
    }
}
