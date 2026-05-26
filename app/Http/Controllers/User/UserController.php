<?php

namespace App\Http\Controllers\User;

use App\Actions\User\ShowUserAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }



    /**
     * Display the specified resource.
     */
    public function show(ShowUserAction $showUserAction): JsonResponse
    {
        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => new UserResource($showUserAction()),
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $updateUserRequest, UpdateUserAction $updateUserAction): JsonResponse
    {
        $user = $updateUserAction($updateUserRequest);

        return response()
            ->json(
                [
                    'message' => 'User updated successfully',
                    'user' => new UserResource($user),
                ], Response::HTTP_OK
            );

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
