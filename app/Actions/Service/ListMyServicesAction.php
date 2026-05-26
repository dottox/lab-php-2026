<?php

namespace App\Actions\Service;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ListMyServicesAction
{
    public function __invoke(): Collection
    {
        $user = auth('user_jwt')->user();
        $professionalProfile = $user->professionalProfile;

        abort_if(!$professionalProfile, 403, 'Debes tener un perfil profesional.');

        return $professionalProfile
            ->services()
            ->latest()
            ->get();
    }
}
