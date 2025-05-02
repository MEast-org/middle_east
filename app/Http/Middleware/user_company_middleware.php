<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class user_company_middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('api')->check()) {
            auth()->shouldUse('api'); // لجعل auth()->user() يشتغل
            return $next($request);
        }

        if (auth('company-api')->check()) {
            auth()->shouldUse('company-api');
            return $next($request);
        }

        return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    }
}
