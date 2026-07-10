<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    protected function paginatedResponse(ResourceCollection $collection, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $collection->response()->getData(true)['data'],
            'meta' => [
                'current_page' => $collection->resource->currentPage(),
                'last_page' => $collection->resource->lastPage(),
                'per_page' => $collection->resource->perPage(),
                'total' => $collection->resource->total(),
            ],
        ]);
    }
}
