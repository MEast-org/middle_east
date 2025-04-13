<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class user_middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // استخدام الحارس employee للتحقق من التوكن
            if (! $user = Auth::guard('api')->user()) {
                return response()->json(['error' => 'Not Authorized as user'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Not Authorized'], 401);
        }

        return $next($request);
    }
}
