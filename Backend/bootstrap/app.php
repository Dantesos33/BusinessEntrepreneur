<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // statefulApi() registers EnsureFrontendRequestsAreStateful,
        // which conditionally runs StartSession + VerifyCsrfToken +
        // EncryptCookies ONLY for requests it recognizes as coming
        // from a stateful frontend domain (via Origin/Referer header
        // matching SANCTUM_STATEFUL_DOMAINS). This single call is
        // the documented, correct way to enable SPA cookie auth on
        // routes/api.php.
        $middleware->statefulApi();

        // Trust the Vite dev server's proxy / X-Forwarded-* headers
        // so Laravel generates correct absolute URLs and detects the
        // request scheme/host accurately in local dev.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // For API routes, always return JSON — and in local/debug
        // mode, include the real exception message + a short trace
        // instead of a generic message, so failures are diagnosable
        // from the Network tab without needing to read storage/logs.
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->is('api/*') || !config('app.debug')) {
                return null; // fall back to default handling
            }

            // 💡 Dynamically capture the correct HTTP status code.
            // AuthenticationException maps to 401, Validation to 422, etc.
            // If it's a generic server crash, default back to 500.
            $statusCode = 500;
            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
            } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                $statusCode = 401;
            } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                $statusCode = 422;
            }

            return response()->json([
                'message' => $e->getMessage() ?: class_basename($e),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(8)->map(fn($t) => [
                    'file' => $t['file'] ?? null,
                    'line' => $t['line'] ?? null,
                    'function' => $t['function'] ?? null,
                ]),
            ], $statusCode); // 🌟 Fixed: Status code is now dynamic (401 for guest users)
        });
    })->create();
