<?php

namespace App\Actions\ProfessionalProfile;

use App\Http\Requests\ProfessionalProfile\StoreProfessionalProfileRequest;
use App\Models\User\ProfessionalProfile;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTGuard;

class StoreProfessionalProfileAction
{
    public function __invoke(
        StoreProfessionalProfileRequest $request
    ): ProfessionalProfile {

        /** @var JWTGuard $guard */
        $guard = auth('user_jwt')->user()->id;

        return ProfessionalProfile::create([
            'user_id' => $guard,
            'bio' => $request->validated('bio'),
        ]);
    }
}
