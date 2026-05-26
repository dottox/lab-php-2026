<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfessionalProfile\ProfessionalProfileController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::middleware('auth:user_jwt')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);

            Route::prefix('services')->group(function () {
                Route::get('/my', [ServiceController::class, 'my']);
                Route::post('/', [ServiceController::class, 'store']);
                Route::get('/{service}', [ServiceController::class, 'show']);
                Route::put('/{service}', [ServiceController::class, 'update']);
                Route::delete('/{service}', [ServiceController::class, 'destroy']);
            });

        });
        Route::prefix('professional-profile')->group(function () {

            Route::post('/', [ProfessionalProfileController::class, 'store']);

            Route::get('/', [ProfessionalProfileController::class, 'show']);

            Route::put('/', [ProfessionalProfileController::class, 'update']);
        });

    });

    Route::middleware('auth:user_jwt')->group(function () {
        Route::get('/me', [UserController::class, 'show']);
        Route::put('/me', [UserController::class, 'update']);

        Route::apiResource('users', UserController::class)
            ->only(['index', 'show', 'update', 'destroy']);
    });
});
