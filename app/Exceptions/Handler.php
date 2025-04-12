<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
     // معالجة أخطاء 404 (للنماذج والروتات)
     $this->renderable(function (ModelNotFoundException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => 'المورد المطلوب غير موجود',
            'type' => 'model_not_found'
        ], 404);
    });

    // معالجة أخطاء التحقق (Validation)
    $this->renderable(function (ValidationException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => 'أخطاء في البيانات المدخلة',
            'errors' => $e->errors(),
            'type' => 'validation_error'
        ], 422);
    });

    // معالجة أخطاء 404 العامة (للروتات غير الموجودة)
    $this->renderable(function (NotFoundHttpException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => 'الرابط المطلوب غير موجود',
            'type' => 'route_not_found'
        ], 404);
    });

    // معالجة باقي الأخطاء (اختياري)
    $this->renderable(function (Throwable $e, $request) {
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع',
                'type' => 'server_error'
            ], 500);
        }
    });
    }
   }
