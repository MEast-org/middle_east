<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\admin;
use App\Models\company;
use App\Models\country;
use App\Models\region;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;


class company_controller extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }



    public function companies()
    {
        $companies = company::with(['country', 'region'])->get();
        return response()->json([
            'companies'=>$companies
        ]);
    }


    public function view_company(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $company = company::with(['country', 'region'])->find($request->id);
        return response()->json([
            'company'=>$company
        ]);
    }


    public function add_company(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'ar_name' => 'required|string|max:255',
            'en_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:companies',
            'password' => 'required|string|min:8',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'phone' => 'required|string|unique:companies',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trade_log' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'state' => 'sometimes|in:active,inactive,pending'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $company = company::create(array_merge(
            $validator->validated(),
            ['password' => Hash::make($request->password)]
        ));

        // رفع الملفات إذا وجدت
        if ($request->hasFile('logo')) {
            $company->logo = $request->file('logo')->store('logo', 'public');
            $company->save();
        }

        if ($request->hasFile('trade_log')) {
            $company->trade_log = $request->file('trade_log')->store('trade_logs', 'public');
            $company->save();
        }



        return response()->json([
            'message' => 'Company successfully created',
            'company' => $company
        ], 201);
    }

    // تحديث بيانات الشركة
    public function update_company(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
            'ar_name' => 'sometimes|required|string|max:255',
            'en_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:companies,email,'.$request->id,
            'password' => 'sometimes|required|string|min:8',
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'region_id' => 'sometimes|nullable|exists:regions,id',
            'phone' => 'sometimes|required|string|unique:companies,phone,'.$request->id,
            'logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trade_log' => 'sometimes|nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'state' => 'sometimes|in:active,inactive,pending'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $company = company::find($request->id);

        if ($request->has('logo')) {
            // إذا تم إرسال حقل flag كقيمة null أو فارغة
            if (is_null($request->logo) || $request->logo === '') {
                if ($company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }
                $company->logo = null;
            }
            // إذا تم رفع ملف جديد
            elseif ($request->hasFile('logo')) {
                if ($company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }
                $company->logo = $request->file('logo')->store('logo', 'public');
            }
        }




        if ($request->has('trade_log')) {
            // إذا تم إرسال حقل flag كقيمة null أو فارغة
            if (is_null($request->trade_log) || $request->trade_log === '') {
                if ($company->trade_log) {
                    Storage::disk('public')->delete($company->trade_log);
                }
                $company->trade_log = null;
            }
            // إذا تم رفع ملف جديد
            elseif ($request->hasFile('trade_log')) {
                if ($company->trade_log) {
                    Storage::disk('public')->delete($company->trade_log);
                }
                $company->trade_log = $request->file('trade_log')->store('trade_logs', 'public');
            }
        }
        if ($request->has('password')) {

            $company->password = Hash::make($request->password);
        }

            $company->update(array_merge(
                $validator->validated(),
                [
                'logo' => $company->logo,
                'trade_log' => $company->trade_log,
                'password' =>$company->password
                ]
            ));


        return response()->json([
            'message' => 'Company updated successfully',
            'company' => $company
        ]);
    }

    // حذف شركة
    public function delete_company(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $company = company::find($request->id);

        // حذف الملفات المرتبطة
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        if ($company->trade_log) {
            Storage::disk('public')->delete($company->trade_log);
        }

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully']);
    }

    // // تغيير حالة الشركة
    // public function changeState(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|exists:companies,id',
    //         'state' => 'required|in:active,inactive,pending'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 400);
    //     }

    //     $company = Company::find($request->id);
    //     $company->state = $request->state;
    //     $company->save();

    //     return response()->json([
    //         'message' => 'Company state updated successfully',
    //         'company' => $company
    //     ]);
    // }



}
