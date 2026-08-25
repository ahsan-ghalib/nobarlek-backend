<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

trait JsonResponseTrait
{
    /**
     * Make a request to the sports API.
     *
     * @param Collection $data
     * @param int $statusCode
     * @param bool $encrypt
     * @return JsonResponse
     */
    public function jsonResponse(Collection | array $data, int $statusCode, bool $encrypt = false): JsonResponse
    {
        $message = match ($statusCode) {
            200 => 'Successfully retrieved data',
            201 => 'Successfully data created',
            204 => 'No data found',
            422 => 'Unprocessable entity',
            404 => 'Page not found',
            400 => 'Bad request',
            500 => 'Internal Server error',
        };

        $data = $encrypt ? Crypt::encrypt($data) : $data;

        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
