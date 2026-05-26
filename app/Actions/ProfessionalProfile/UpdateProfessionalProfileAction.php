<?php

namespace App\Actions\ProfessionalProfile;

use App\Http\Requests\ProfessionalProfile\UpdateProfessionalProfileRequest;
use App\Models\User\ProfessionalProfile;
use Illuminate\Support\Facades\Auth;

class UpdateProfessionalProfileAction
{
    public function __invoke(
        UpdateProfessionalProfileRequest $request
    ): ProfessionalProfile {

        $profile = ProfessionalProfile::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $profile->update(
            $request->validated()
        );

        return $profile->refresh();
    }
}
