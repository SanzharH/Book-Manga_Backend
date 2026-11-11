<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $startedAt = hrtime(true);
        $statusCode = 500;

        try {
            $response = $next($request);
            $statusCode = $response->getStatusCode();

            return $response;
        } catch (Throwable $exception) {
            $statusCode = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof AuthenticationException => 401,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof ModelNotFoundException => 404,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            throw $exception;
        } finally {
            Log::info('API request completed', [
                'method' => $request->method(),
                'url' => $request->getPathInfo(),
                'user_id' => $request->user()?->getAuthIdentifier(),
                'status_code' => $statusCode,
                'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            ]);
            if ($statusCode >= 500) {
            Log::error('API request failed', [
                'method' => $request->method(),
                'url' => $request->getPathInfo(),
                'user_id' => $request->user()?->getAuthIdentifier(),
                'status_code' => $statusCode,
                'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            ]);
            }
        }
    }
}