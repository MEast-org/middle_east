<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class login_controller extends Controller
{
   /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // محاولة تسجيل الدخول كـ Doctor
        if ($token = $this->attemptLogin($validator->validated(), 'admin-api')) {
            return $this->createNewToken($token, 'admin','admin-api');
        }

        if ($token = $this->attemptLogin($validator->validated(), 'company-api')) {
            return $this->createNewToken($token, 'company','company-api');
        }

        // محاولة تسجيل الدخول كـ Admin
        if ($token = $this->attemptLogin($validator->validated(), 'api')) {
            return $this->createNewToken($token, 'user','api');
        }


        return response()->json(['error' => 'Unauthorized....'], 401);
    }

    private function attemptLogin($credentials, $guard)
    {
        // config(['auth.defaults.guard' => $guard]);

        if ($token = auth()->guard($guard)->attempt($credentials)) {
            return $token;
        }

        return false;
    }

    protected function createNewToken($token, $userType,$guard)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard($guard)->factory()->getTTL() * 60 * 24* 7* 50,
            'user' => auth()->guard($guard)->user(),
            'user_type' => $userType
        ]);
    }

}

