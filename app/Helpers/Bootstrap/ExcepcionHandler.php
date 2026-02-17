<?php

namespace App\Helpers\Bootstrap;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExcepcionHandler
{
    public function __invoke(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(fn () => true);

        // Render minimal JSON for HTTP exceptions (e.g. 404) without stack traces.
        $exceptions->renderable(fn (HttpExceptionInterface $e, Request $request) 
            => $this->handleHttpException($e, $request));

        // Render validation exceptions with structured 422 responses.
        $exceptions->renderable(fn (ValidationException $e, Request $request) 
            => $this->handleValidationException($e, $request));

        // Render authentication exceptions as 401 responses.
        $exceptions->renderable(fn (AuthenticationException $e, Request $request) 
            => $this->handleAuthenticationException($e, $request));

        // Generic handler for other exceptions — use helper to log and render.
        $exceptions->renderable(fn (Throwable $e, Request $request) 
            => $this->handleThrowable($e, $request));
    }

    protected function handleHttpException(HttpExceptionInterface $e, Request $request)
    {
        $status = $e->getStatusCode();
        $message = $status === 404 ? 'Not Found.' : 'Error.';

        $errorId = $this->logException('HTTP exception handled', $e, $request, ['status' => $status]);

        return response()->json(['message' => $message, 'error_id' => $errorId], $status);
    }

    protected function handleThrowable(Throwable $e, Request $request)
    {
        $errorId = $this->logException('Unhandled exception', $e, $request);

        return response()->json(['message' => 'Server Error.', 'error_id' => $errorId], 500);
    }

    protected function handleValidationException(ValidationException $e, Request $request)
    {
        $errorId = $this->logException('Validation exception', $e, $request);

        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $e->errors(),
            'error_id' => $errorId,
        ], 422);
    }

    protected function handleAuthenticationException(AuthenticationException $e, Request $request)
    {
        $errorId = $this->logException('Authentication exception', $e, $request);

        return response()->json([
            'message' => 'Unauthenticated.',
            'error_id' => $errorId,
        ], 401);
    }

    /**
     * Log an exception with a generated error id and return that id.
     * Extra context can be provided in the third parameter.
     */
    protected function logException(string $message, Throwable $e, Request $request, array $extra = []): string
    {
        $errorId = (string) Str::uuid();

        $payload = array_merge([
            'error_id' => $errorId,
            'exception_class' => get_class($e),
            'exception_message' => $e->getMessage(),
            'path' => $request->path(),
            'method' => $request->method(),
            'user_id' => optional($request->user())->id,
        ], $extra);

        // Keep log entry scalar-only to avoid serializing stack traces or objects.
        Log::error($message, $payload);

        return $errorId;
    }
}
