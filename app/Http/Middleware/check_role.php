<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class check_role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$role): Response
    {
        {
            if (!auth('admin-api')->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if (auth('admin-api')->user()->role !== $role) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return $next($request);
        }
    }
}
