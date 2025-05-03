<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\country;
use App\Models\region;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;


class country_controller extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function countries()
{
    $countries = country::with('regions')->get();
    return response()->json(['countries'=>$countries]);
}



public function countries_state()
{
    $validator = Validator::make(request()->all(), [
        'state' => 'required|in:active,inactive'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $query = country::with('regions');

    if (request()->has('state')) {
        $query->where('state', request()->state);
    }

    $countries = $query->get();

    return response()->json([
        'success' => true,
        'countries' => $countries
    ]);
}

public function add_country(Request $request)
{
    $validator = Validator::make($request->all(), [
        'en_name' => 'required|string|max:100',
        'ar_name' => 'required|string|max:100',
        'flag' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }


    $country = country::create(array_merge(
        $validator->validated()
    ));

    if ($request->hasFile('flag')) {
        $country->flag = $request->file('flag')->store('flags', 'public');
        $country->save();
    }

    return response()->json([
        'message' => 'country successfully created',
        'country' => $country
    ], 201);
}

public function view_country(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:countries,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $country = country::find($request->id);
        if (!$country) {
            return response()->json(['message' => 'country not found'], 404);
        }

        return response()->json([
            'country'=>$country,
        ]);
    }

    public function update_country(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'id' => 'required|exists:countries,id',
            'en_name' => 'sometimes|required|string|max:100',
            'ar_name' => 'sometimes|required|string|max:100',
            'flag' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'state' => 'sometimes|required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $country = country::find($request->id);
        if (!$country) {
            return response()->json(['error' => 'country not found'], 404);
        }

        // if (is_null($request->flag) || $request->flag === '') {
        //     if ($country->flag) {
        //         Storage::disk('public')->delete($country->flag);
        //     }
        //     $country->flag = null; // تعيين قيمة null في قاعدة البيانات
        // }
    //      if ($request->hasFile('flag')) {
    //     // إذا تم رفع ملف صورة جديد
    //     if ($country->flag) {
    //         Storage::disk('public')->delete($country->flag);
    //     }
    //     if ($request->flag == null || $request->flag == ''){
    //         $country->flag = null;
    //     } else{
    //     $flag = $request->file('flag')->store('flags', 'public');
    //     $country->flag = $flag;
    //   }}

     // التعامل مع الصورة
     if ($request->has('flag')) {
        // إذا تم إرسال حقل flag كقيمة null أو فارغة
        if (is_null($request->flag) || $request->flag === '') {
            if ($country->flag) {
                Storage::disk('public')->delete($country->flag);
            }
            $country->flag = null;
        }
        // إذا تم رفع ملف جديد
        elseif ($request->hasFile('flag')) {
            if ($country->flag) {
                Storage::disk('public')->delete($country->flag);
            }
            $country->flag = $request->file('flag')->store('flags', 'public');
        }
    }

        $country->update(array_merge(
            $validator->validated(),
            [
            'flag' => $country->flag,
            ]
        ));

        return response()->json(['message' => 'country updated successfully', 'country' => $country], 200);

    }

    public function delete_country(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:countries,id'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $country = Country::find($request->id);

    if ($country->flag) {
        Storage::disk('public')->delete($country->flag);
    }

    $country->delete();

    return response()->json([
        'success' => true,
        'message' => 'country deleted sucssess'
    ]);
}

public function country_regions(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:countries,id'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $regions = country::find($request->id)->regions()->get();

    return response()->json([
        'success' => true,
        'regions' => $regions
    ]);
}

////////////////////// manage regions//////////////////////////

public function regions()
{
    $regions = region::with('country')->get();
    return response()->json($regions);
}

/**
 * إضافة منطقة جديدة
 */
public function add_region(Request $request)
{
    $validator = Validator::make($request->all(), [
        'country_id' => 'required|exists:countries,id',
        'en_name' => 'required|string|max:100',
        'ar_name' => 'required|string|max:100',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $region = region::create($validator->validated());

    return response()->json([
        'message' => 'Region successfully created',
        'region' => $region
    ], 201);
}

/**
 * عرض منطقة معينة
 */
public function view_region(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:regions,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $region = region::with('country')->find($request->id);

    return response()->json([
        'region' => $region,
    ]);
}

/**
 * تحديث بيانات المنطقة
 */
public function update_region(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:regions,id',
        'en_name' => 'sometimes|required|string|max:100',
        'ar_name' => 'sometimes|required|string|max:100',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $region = region::find($request->id);
    $region->update($validator->validated());

    return response()->json([
        'message' => 'Region updated successfully',
        'region' => $region
    ], 200);
}

/**
 * حذف منطقة
 */
public function delete_region(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:regions,id'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    region::find($request->id)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Region deleted successfully'
    ]);
}


}
