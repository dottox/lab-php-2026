<?php

namespace App\Actions\Service;

use App\Models\Service\Service;
use Illuminate\Support\Facades\Auth;

class DeleteServiceAction
{
    public function __invoke(Service $service): void
    {
        $service->delete();
    }
}
