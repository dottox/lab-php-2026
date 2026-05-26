<?php

namespace App\Actions\Service;

use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service\Service;
use Illuminate\Support\Facades\Auth;

class UpdateServiceAction
{
    public function __invoke(UpdateServiceRequest $request, Service $service): Service
    {
        $service->update($request->validated());

        return $service->refresh();
    }
}
