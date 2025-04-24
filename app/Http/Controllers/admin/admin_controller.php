<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\admin;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\usermail;



class admin_controller extends Controller
{
       /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function update_profile(Request $request)
    {
        $admin = auth('admin-api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:admins,email,'.$admin->id,
            'password' => 'sometimes|required|string|min:8',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        if (is_null($request->image) || $request->image === '') {
            if ($admin->image) {
                // حذف الصورة القديمة من التخزين
                Storage::disk('public')->delete($admin->image);
            }
            $admin->image = null; // تعيين قيمة null في قاعدة البيانات
        } elseif ($request->hasFile('image')) {
        // إذا تم رفع ملف صورة جديد
        if ($admin->image) {
            Storage::disk('public')->delete($admin->image);
        }
        $image = $request->file('image')->store('admins', 'public');
        $admin->image = $image;
      }

        $admin->update(array_merge(
            $validator->validated(),
            $request->password ? ['password' => Hash::make($request->password)] : [],
            [
            'image' => $admin->image,
            ]
        ));

        return response()->json(['message' => 'profile updated successfully', 'admin' => $admin], 200);



    }


    public function profile()
    {
        return response()->json(['profile'=>auth('admin-api')->user()]);
    }




    public function logout()
    {
        auth('admin-api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }



    public function admins()
    {
        if (auth('admin-api')->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $admins = Admin::all();
        return response()->json($admins);
    }


    public function add_admin(Request $request)
    {
        if (auth('admin-api')->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:admins',
            'password' => 'required|string|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|in:super_admin,admin'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $admin = Admin::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        if ($request->hasFile('image')) {
            $admin->image = $request->file('image')->store('admins', 'public');
            $admin->save();
        }

        return response()->json([
            'message' => 'Admin successfully created',
            'admin' => $admin
        ], 201);
    }



    public function view_admin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $admin = Admin::find($request->id);
        if (!$admin) {
            return response()->json(['message' => 'Provider not found'], 404);
        }


        // يمكن للـ Super Admin أو الأدمن نفسه رؤية البيانات
        if (auth('admin-api')->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'admin'=>$admin,
        ]);
    }

    public function update_admin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'name' => 'sometimes|required|string|between:2,100',
            'email' => 'sometimes|required|string|email|max:100|unique:admins,email,'.$request->id,
            'password' => 'sometimes|required|string|min:8',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'sometimes|required|in:super_admin,admin'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (auth('admin-api')->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $admin = Admin::find($request->id);
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }





        if (is_null($request->image) || $request->image === '') {
            if ($admin->image) {
                // حذف الصورة القديمة من التخزين
                Storage::disk('public')->delete($admin->image);
            }
            $admin->image = null; // تعيين قيمة null في قاعدة البيانات
        } elseif ($request->hasFile('image')) {
        // إذا تم رفع ملف صورة جديد
        if ($admin->image) {
            Storage::disk('public')->delete($admin->image);
        }
        $image = $request->file('image')->store('admins', 'public');
        $admin->image = $image;
      }

        $admin->update(array_merge(
            $validator->validated(),
            $request->password ? ['password' => Hash::make($request->password)] : [],
            [
            'image' => $admin->image,
            ]
        ));

        return response()->json(['message' => 'admin updated successfully', 'admin' => $admin], 200);

    }


    public function delete_admin(Request $request)
    {

        if (auth('admin-api')->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id'=>'required',
        ]);

        $admin = Admin::find($request->id);
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        if ($admin->image) {
            Storage::disk('public')->delete($admin->image);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin successfully deleted']);
    }


////////////////////////////////////////////////////////////////////


public function sendTestEmail()
    {
        try {
            // بيانات الرسالة
            // $emailData = [
            //     'code' => '123456',
            //     'expiry' => '30 دقيقة'
            // ];
            $code = '123456';

            // إرسال إلى بريدك (استبدل example@test.com ببريدك)
            Mail::to('monepmohammad8@gmail.com.com')->send(new usermail( code: '123456',
            expiry: '15 دقيقة'));

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرسالة بنجاح!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الإرسال: ' . $e->getMessage()
            ], 500);
        }
    }



}
