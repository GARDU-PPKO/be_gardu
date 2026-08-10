<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Sukses tanpa data.
     */
    public static function ok(string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json(self::meta(true, $status, $message), $status);
    }

    /**
     * Sukses dengan data.
     */
    public static function success(mixed $data, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            ...self::meta(true, $status, $message),
            'data' => $data,
        ], $status);
    }

    /**
     * Sukses dengan pagination.
     */
    public static function paginated(LengthAwarePaginator $paginator, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            ...self::meta(true, $status, $message),
            'data' => array_values($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $status);
    }

    /**
     * Error dengan daftar pesan per field (mis. hasil validasi).
     */
    public static function error(string $message, int $status, array $errors = []): JsonResponse
    {
        $payload = self::meta(false, $status, $message);

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array{meta: array{success: bool, status_code: string, message: string}}
     */
    private static function meta(bool $success, int $status, string $message): array
    {
        return [
            'meta' => [
                'success' => $success,
                'status_code' => (string) $status,
                'message' => $message,
            ],
        ];
    }
}
