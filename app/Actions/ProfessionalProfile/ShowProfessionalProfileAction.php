<?php

namespace App\Actions\ProfessionalProfile;

use App\Models\User\ProfessionalProfile;
use Illuminate\Support\Facades\Auth;

class ShowProfessionalProfileAction
{
    public function __invoke(): ?ProfessionalProfile
    {
        return ProfessionalProfile::query()
            ->where('user_id', Auth::id())
            ->first();
    }
}
