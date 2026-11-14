<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Renders API errors as RFC 9457 Problem Details (application/problem+json).
 */
class ProblemDetailsResponse
{
    public static function from(Throwable $exception, Request $request): JsonResponse
    {
        [$status, $title, $detail, $headers, $errors] = self::describe($exception);

        $body = array_filter([
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->path(),
            'errors' => $errors,
        ], fn (mixed $value): bool => $value !== null);

        return response()->json($body, $status, [
            ...$headers,
            'Content-Type' => 'application/problem+json',
        ]);
    }

    /**
     * @return array{0: int, 1: string, 2: ?string, 3: array<string, string>, 4: ?array<string, mixed>}
     */
    private static function describe(Throwable $exception): array
    {
        return match (true) {
            $exception instanceof ApiProblemException => [
                $exception->status, $exception->title, $exception->detail, $exception->headers, null,
            ],
            $exception instanceof ValidationException => [
                422, 'Validation failed', $exception->getMessage(), [], $exception->errors(),
            ],
            $exception instanceof AuthenticationException => [
                401, 'Unauthorized', 'Missing, invalid, or revoked token.', [], null,
            ],
            $exception instanceof NotFoundHttpException => [
                404, 'Not Found', null, [], null,
            ],
            $exception instanceof HttpException => [
                $exception->getStatusCode(),
                self::titleFor($exception->getStatusCode()),
                $exception->getMessage() ?: null,
                $exception->getHeaders(),
                null,
            ],
            default => [500, 'Internal Server Error', app()->hasDebugModeEnabled() ? $exception->getMessage() : null, [], null],
        };
    }

    private static function titleFor(int $status): string
    {
        return match ($status) {
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            413 => 'Payload Too Large',
            429 => 'Too Many Requests',
            default => 'Error',
        };
    }
}
