<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User\User;

class RegisterUserAction
{
    public function __invoke(RegisterUserRequest $request): User
    {
        return User::create($request->validated());
    }
}
