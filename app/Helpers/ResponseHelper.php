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

    public static function returnValidationError($errors): JsonResponse
{
    // إذا كان كائن يحتوي على دالة errors()
    if (is_object($errors) && method_exists($errors, 'errors')) {
        $errors = $errors->errors(); // نحصل على MessageBag
    }

    // إذا كان كائن MessageBag نحوله إلى مصفوفة
    if (is_object($errors) && method_exists($errors, 'toArray')) {
        $errors = $errors->toArray();
    }

    // هنا سنقوم بإرجاع جميع الأخطاء
    return self::error(__('validation.errorValidation'), $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
}
}
