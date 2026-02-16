<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )->withMiddleware()->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => true);

        // Render minimal JSON for HTTP exceptions (e.g. 404) without stack traces.
        $exceptions->renderable(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();
            $message = $status === 404 ? 'Not Found.' : 'Error.';
            $errorId = (string) Str::uuid();

            // Log only scalar/primitive details to avoid serializing the exception (no stacktrace).
            Log::error('HTTP exception handled', [
                'error_id' => $errorId,
                'status' => $status,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => optional($request->user())->id,
            ]);

            return response()->json(['message' => $message, 'error_id' => $errorId], $status);
        });

        // Generic handler for all other exceptions — log limited details but do not expose them in response.
        $exceptions->renderable(function (Throwable $e, Request $request) {
            $errorId = (string) Str::uuid();

            Log::error('Unhandled exception', [
                'error_id' => $errorId,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => optional($request->user())->id,
            ]);

            return response()->json(['message' => 'Server Error.', 'error_id' => $errorId], 500);
        });
    })->create();
