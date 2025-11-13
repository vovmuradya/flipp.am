<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, ?string $message = null, int $code = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => (object) $meta,
        ], $code);
    }

    protected function created(mixed $data = null, ?string $message = null, array $meta = []): JsonResponse
    {
        return $this->success($data, $message, 201, $meta);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function unauthorized(?string $message = null): JsonResponse
    {
        return $this->error($message ?? __('Неавторизованный запрос'), 401);
    }

    protected function forbidden(?string $message = null): JsonResponse
    {
        return $this->error($message ?? __('Доступ запрещён'), 403);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json([], 204);
    }
}
