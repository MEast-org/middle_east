<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\ResponseHelper;
use App\Models\User;
use App\Models\company;

class auth_user_controller extends Controller
{
    public function __construct()
    {
        $this->middleware('user_company_auth')->except(['register_user', 'send_reset_code','reset_password']);
    }


    public function register_user(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:companies,email',
            'password' => 'required|min:8|confirmed',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'gender' => $validated['gender'] ?? null,
            'birthday' => $validated['birthday'] ?? null,
            'country_id' => $validated['country_id'] ?? null,
            'region_id' => $validated['region_id'] ?? null,
            'photo' => $validated['photo'] ?? null,
        ]);

        $token = JWTAuth::fromUser($user);

        return ResponseHelper::success('User registered successfully', [
            'user' => $user,
            'token' => $token,
        ]);
    }



// عرض الملف الشخصي (مشترك)
public function profile()
{
    $user = auth()->user();
    return ResponseHelper::success('Profile retrieved successfully', $user);
}

public function updateUserProfile(Request $request)
{
    $user = auth('api')->user();

    $validated = $request->validate([
        'name' => 'nullable|string|max:255',
        'photo' => 'nullable|image|max:2048',
        'gender' => 'nullable|in:male,female',
        'birthday' => 'nullable|date',
        'country_id' => 'nullable|exists:countries,id',
        'region_id' => 'nullable|exists:regions,id',
    ]);

    if ($request->hasFile('photo')) {
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }
        $validated['photo'] = $request->file('photo')->store('users', 'public');
    }

    $user->update($validated);

    return ResponseHelper::success('Profile updated successfully', $user);
}



// تسجيل الخروج
public function logout()
{
    JWTAuth::invalidate(JWTAuth::getToken());
    return ResponseHelper::success('Logged out successfully');
}

// إرسال كود استعادة كلمة السر (تجريبي)
public function send_reset_code(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $validated['email'])->first()
        ?? company::where('email', $validated['email'])->first();

    if (!$user) {
        return ResponseHelper::error('Email not found', 404);
    }

    $code = rand(100000, 999999);
    $user->update([
        'verification_code' => '123456',
        'code_expires_at' => now()->addMinutes(10)
    ]);

    // يُفضل إرسال الكود عبر البريد هنا

    return ResponseHelper::success('Verification code sent',);
}

// إعادة تعيين كلمة السر
public function reset_password(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'verification_code' => 'required|string',
        'password' => 'required|confirmed|min:8'
    ]);

    $user = User::where('email', $validated['email'])->first()
        ?? company::where('email', $validated['email'])->first();

    if (!$user || $user->verification_code !== $validated['verification_code'] || now()->gt($user->code_expires_at)) {
        return ResponseHelper::error('Invalid or expired code', 422);
    }

    $user->update([
        'password' => bcrypt($validated['password']),
        'verification_code' => null,
        'code_expires_at' => null,
    ]);

    return ResponseHelper::success('Password reset successfully');
}


}
