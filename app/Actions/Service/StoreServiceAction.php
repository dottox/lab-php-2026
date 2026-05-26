<?php

namespace App\Actions\Service;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Models\Service\Service;

class StoreServiceAction
{
    public function __invoke(StoreServiceRequest $request): Service
    {
        $data = $request->validated();
        $user = auth('user_jwt')->user();
        $professionalProfile = $user->professionalProfile;

        // if(!$professionalProfile) {
        //     throw new \Exception('Professional profile not found for the authenticated user.');
        // }

        abort_if(!$professionalProfile, 404, 'Professional profile not found for the authenticated user.');

        return Service::create([
            ...$data,
            'professional_id' => $professionalProfile->id,
        ]);
    }
}
