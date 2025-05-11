<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait BaseResponse
{

    /**
     * Building success response
     * @param $data
     * @param int $code
     * @return JsonResponse
     */
    public function successResponse(string $message = 'success', $data = null, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
    /**
     * Building success response
     * @param $data
     * @param int $code
     * @return JsonResponse
     */

    public function errorResponse(string $message = 'An error occurred.', $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }

    public function exceptionResponse(Exception $e, string $defaultMessage = 'An error occurred.'): JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $exceptionCode = $e->getCode();

        if (is_int($exceptionCode) && $exceptionCode >= 100 && $exceptionCode < 600) {
            $statusCode = $exceptionCode;
        }

        $message = config('app.debug') ? __($e->getMessage()) : $defaultMessage;

        Log::error('Exception occurred', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $response = [
            'status' => false,
            'message' => $message,
            'data' => null,
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }

        return response()->json($response, $statusCode);
    }

}
