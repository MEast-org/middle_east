<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ResponseHelper
{
    public static function success(string $msg, mixed $data = null): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $msg,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    public static function error(string $msg, mixed $data = null, int $status_code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $msg,
            'errors' => $data,
        ], $status_code);
    }

    public static function returnValidationError($validator): JsonResponse
    {
        return self::error(__('validation.errorValidation'), $validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
