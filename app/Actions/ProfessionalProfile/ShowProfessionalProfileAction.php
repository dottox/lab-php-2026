<?php

namespace App\Actions\ProfessionalProfile;

use App\Models\User\ProfessionalProfile;

class ShowProfessionalProfileAction
{
    public function __invoke(): ?ProfessionalProfile
    {
        return ProfessionalProfile::query()
            ->where('user_id', auth('user_jwt')->id())
            ->first();
    }
}
