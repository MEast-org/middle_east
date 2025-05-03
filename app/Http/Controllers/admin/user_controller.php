<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class user_controller extends Controller
{

       /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function users()
{
    $users = User::with(['country', 'region'])->paginate(10);
    return response()->json([
        'users' => $users
    ]);
}

public function view_user(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::with(['country', 'region'])->find($request->id);
    return response()->json([
        'user' => $user
    ]);
}

public function add_user(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'birthday' => 'nullable|date_format:Y-m-d',
        'gender' => 'required|in:male,female',
        'country_id' => 'nullable|exists:countries,id',
        'region_id' => 'nullable|exists:regions,id',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::create(array_merge(
        $validator->validated(),
        ['password' => Hash::make($request->password),
        'verified_at'=> now(),
        ]
    ));

    if ($request->hasFile('photo')) {
        $user->photo = $request->file('photo')->store('photos', 'public');
        $user->save();
    }

    return response()->json([
        'message' => 'User successfully created',
        'user' => $user
    ], 201);
}

public function update_user(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:users,id',
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$request->id,
        'password' => 'sometimes|required|string|min:8',
        'gender' => 'sometimes|required|in:male,female',
        'birthday' => 'sometimes|nullable|date_format:Y-m-d',
        'country_id' => 'sometimes|nullable|exists:countries,id',
        'region_id' => 'sometimes|nullable|exists:regions,id',
        'photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::find($request->id);

    if ($request->has('photo')) {
        if (is_null($request->photo) || $request->photo === '') {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = null;
        } elseif ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('photos', 'public');
        }
    }

    if ($request->has('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->update(array_merge(
        $validator->validated(),
        [
            'photo' => $user->photo,
            'password' => $user->password
        ]
    ));

    return response()->json([
        'message' => 'User updated successfully',
        'user' => $user
    ]);
}

public function delete_user(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::find($request->id);

    if ($user->photo) {
        Storage::disk('public')->delete($user->photo);
    }

    $user->delete();

    return response()->json(['message' => 'User deleted successfully']);
}
public function filter_users(Request $request)
{
    $search = $request->search;

    $query = User::with(['country', 'region']);

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('id', '=', "$search")
              ->orWhere('gender', 'like', "%$search%")
              ->orWhere('birthday', 'like', "%$search%")
              ->orWhereHas('country', function($q) use ($search) {
                  $q->where('ar_name', 'like', "%$search%")
                    ->orWhere('en_name', 'like', "%$search%");
              })
              ->orWhereHas('region', function($q) use ($search) {
                  $q->where('ar_name', 'like', "%$search%")
                    ->orWhere('en_name', 'like', "%$search%");
              });
        });
    }

    // paginate with 10 results per page
    $users = $query->paginate(10);

    return response()->json([
        'users' => $users
    ],201);
}



}
