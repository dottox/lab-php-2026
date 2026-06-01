<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

final class ApiExceptionRenderer
{
    public static function render(
        string $error,
        string $message,
        int $status,
        mixed $details = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error' => [
                'type' => $error,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }
}
