<?php

namespace App\Actions\Service;

use App\Exceptions\ApiException;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Models\Service\Service;
use Illuminate\Http\Response;

class StoreServiceAction
{
    public function __invoke(StoreServiceRequest $request): Service
    {
        $data = $request->validated();
        $user = auth('user_jwt')->user();
        $professionalProfile = $user->professionalProfile;

        if (! $professionalProfile) {
            throw new ApiException(
                error: 'ProfessionalProfileRequired',
                message: 'Professional profile is required to create services.',
                status: Response::HTTP_FORBIDDEN
            );
        }

        if (! empty($data['company_id'])) {
            if (! $professionalProfile->companies()
                    ->whereKey($data['company_id'])
                    ->exists()) {
                throw new ApiException(
                    error: 'CompanyForbidden',
                    message: 'No puedes asociar una empresa que no te pertenece.',
                    status: Response::HTTP_FORBIDDEN
                );
            }
        }

        return Service::create([
            ...$data,
            'professional_id' => $professionalProfile->id,
        ]);
    }
}
