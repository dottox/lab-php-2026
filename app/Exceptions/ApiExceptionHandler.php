<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionHandler
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (ValidationException $e): JsonResponse {
            $errors = $e->errors();

            $flat = [];

            foreach ($errors as $messages) {
                foreach ((array) $messages as $message) {
                    if (is_string($message) && trim($message) !== '') {
                        $flat[] = $message;
                    }
                }
            }

            return ApiExceptionRenderer::render(
                error: 'ValidationError',
                message: $flat[0] ?? 'Error de validación.',
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
                details: $errors
            );
        });

        $exceptions->render(function (AuthenticationException $e): JsonResponse {
            return ApiExceptionRenderer::render(
                error: 'Unauthorized',
                message: 'Token inválido o expirado.',
                status: Response::HTTP_UNAUTHORIZED
            );
        });

        $exceptions->render(function (AuthorizationException $e): JsonResponse {
            return ApiExceptionRenderer::render(
                error: 'Forbidden',
                message: 'No tienes permisos para realizar esta acción.',
                status: Response::HTTP_FORBIDDEN
            );
        });

        $exceptions->render(function (ThrottleRequestsException $e): JsonResponse {
            return ApiExceptionRenderer::render(
                error: 'TooManyRequests',
                message: 'Demasiadas solicitudes. Intenta más tarde.',
                status: Response::HTTP_TOO_MANY_REQUESTS
            );
        });

        $exceptions->render(function (ApiException $e): JsonResponse {
            return ApiExceptionRenderer::render(
                error: $e->error(),
                message: $e->getMessage(),
                status: $e->status(),
                details: $e->details()
            );
        });

        $exceptions->render(function (ModelNotFoundException $e): JsonResponse {
            return ApiExceptionRenderer::render(
                error: 'NotFound',
                message: 'Recurso no encontrado.',
                status: Response::HTTP_NOT_FOUND
            );
        });

        $exceptions->render(function (HttpExceptionInterface $e): JsonResponse {
            $status = $e->getStatusCode();

            $message = match ($status) {
                Response::HTTP_NOT_FOUND => 'Recurso no encontrado.',
                Response::HTTP_FORBIDDEN => 'No tienes permisos para realizar esta accion.',
                Response::HTTP_METHOD_NOT_ALLOWED => 'Método HTTP no permitido para este endpoint.',
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Tipo de contenido no soportado.',
                default => 'Ocurrió un error al procesar la solicitud.',
            };

            $error = match ($status) {
                Response::HTTP_NOT_FOUND => 'NotFound',
                Response::HTTP_FORBIDDEN => 'Forbidden',
                Response::HTTP_METHOD_NOT_ALLOWED => 'MethodNotAllowed',
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE => 'UnsupportedMediaType',
                default => 'HttpError',
            };

            return ApiExceptionRenderer::render(
                error: $error,
                message: $message,
                status: $status
            );
        });

        $exceptions->render(function (Throwable $e): JsonResponse {
            Log::error('Unhandled API exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return ApiExceptionRenderer::render(
                error: 'InternalServerError',
                message: 'Ocurrió un error interno. Intenta nuevamente más tarde.',
                status: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        });
    }
}
