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

class auth_company_controller extends Controller
{
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('company_auth:company-api')->except(['register_company']);
    }


    public function register_company(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:companies,email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'country_id' => 'nullable|exists:countries,id',
        'region_id' => 'nullable|exists:regions,id',
        'phone' => 'nullable|string',
        'logo' => 'nullable|image|mimes:jpg,jpeg,png',
        'trade_log' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
    ]);

    if ($request->hasFile('logo')) {
        $validated['logo'] = $request->file('logo')->store('logo', 'public');
    }
    if ($request->hasFile('trade_log')) {
        $validated['trade_log'] = $request->file('trade_log')->store('trade_logs', 'public');
    }

    $company = company::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'country_id' => $validated['country_id'] ?? null,
        'region_id' => $validated['region_id'] ?? null,
        'phone' => $validated['phone'] ?? null,
        'logo' => $validated['logo'] ?? null,
        'trade_log' => $validated['trade_log'] ?? null,
    ]);

    $token = JWTAuth::fromUser($company);

    return ResponseHelper::success('Company registered successfully', [
        'company' => $company,
        'token' => $token,
    ]);
}

public function updateCompanyProfile(Request $request)
{
    $company = auth('company-api')->user();

    $validated = $request->validate([
        'name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'country_id' => 'nullable|exists:countries,id',
        'region_id' => 'nullable|exists:regions,id',
        'logo' => 'nullable|image|max:2048',
        'trade_log' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
    ]);

    if ($request->hasFile('logo')) {
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }
        $validated['logo'] = $request->file('logo')->store('logo', 'public');
    }

    if ($request->hasFile('trade_log')) {
        if ($company->trade_log && Storage::disk('public')->exists($company->trade_log)) {
            Storage::disk('public')->delete($company->trade_log);
        }
        $validated['trade_log'] = $request->file('trade_log')->store('trade_logs', 'public');
    }

    $company->update($validated);

    return ResponseHelper::success('Company profile updated successfully', $company);
}




}
