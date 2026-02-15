<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function getArrayError(string $field, string $message): array
    {
        return [
            'errors' => [
                $field => [$message],
            ],
        ];
    }

    protected function getErrorResponse(
        string $field,
        string $message,
        int $status = 422
    ): JsonResponse {
        $data = $this->getArrayError($field, $message);

        return response()->json($data, $status);
    }
}
