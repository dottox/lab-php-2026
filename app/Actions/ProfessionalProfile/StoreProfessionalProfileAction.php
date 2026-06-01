<?php

namespace App\Actions\ProfessionalProfile;

use App\Exceptions\ApiException;
use App\Http\Requests\ProfessionalProfile\StoreProfessionalProfileRequest;
use App\Models\User\ProfessionalProfile;
use Illuminate\Http\Response;
use Tymon\JWTAuth\JWTGuard;

class StoreProfessionalProfileAction
{
    public function __invoke(
        StoreProfessionalProfileRequest $request
    ): ProfessionalProfile {

        /** @var JWTGuard $guard */
        $user = auth('user_jwt')->user();

        if (ProfessionalProfile::where('user_id', $user->id)->exists()) {
            throw new ApiException(
                error: 'ProfessionalProfileAlreadyExists',
                message: 'El perfil profesional ya existe para este usuario.',
                status: Response::HTTP_CONFLICT
            );
        }

        return ProfessionalProfile::create([
            'user_id' => $user->id,
            'bio' => $request->validated('bio'),
        ]);
    }
}
