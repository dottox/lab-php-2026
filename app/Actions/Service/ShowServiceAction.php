<?php

namespace App\Actions\Service;

use App\Models\Service\Service;
use Illuminate\Support\Facades\Auth;

class ShowServiceAction
{
    public function __invoke(Service $service): Service
    {
        return $service;
    }
}
