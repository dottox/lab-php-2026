<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User\User;
use Illuminate\Support\Str;

class RegisterUserAction
{
    public function __invoke(RegisterUserRequest $request): User
    {
        $uuid = (string) Str::uuid();
        $data = $request->validated();
        $data['id'] = $uuid;
        return User::create($data);
    }
}
